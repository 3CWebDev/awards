<?php

namespace Drupal\commerce_fl_tax\Plugin\Commerce\TaxType;


use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_tax\Resolver\ChainTaxRateResolverInterface;
use Drupal\commerce_tax\TaxZone;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\commerce_tax\Plugin\Commerce\TaxType\LocalTaxTypeBase;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Provides the County tax type.
 *
 * @CommerceTaxType(
 *   id = "commerce_fl_tax",
 *   label = "Florida Sales Tax",
 * )
 */
class FloridaTax extends LocalTaxTypeBase {

  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * Constructs a new FloridaTax object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   The rounder.
   * @param \Drupal\commerce_tax\Resolver\ChainTaxRateResolverInterface $chain_rate_resolver
   *   The chain tax rate resolver.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_generator
   *   The UUID generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EventDispatcherInterface $event_dispatcher, RounderInterface $rounder, ChainTaxRateResolverInterface $chain_rate_resolver, UuidInterface $uuid_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $event_dispatcher, $rounder, $chain_rate_resolver);

    $this->uuidGenerator = $uuid_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('entity_type.manager'),
        $container->get('event_dispatcher'),
        $container->get('commerce_price.rounder'),
        $container->get('commerce_tax.chain_tax_rate_resolver'),
        $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
            'display_label' => 'tax',
            'round' => TRUE,
            'rates' => [],
            'territories' => [],
        ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    parent::setConfiguration($configuration);

    foreach ($this->configuration['rates'] as &$rate) {
      if (isset($rate['amount'])) {
        // The 'amount' key was renamed to 'percentage' in 2.0-rc2.
        $rate['percentage'] = $rate['amount'];
        unset($rate['amount']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['display_label'] = [
        '#type' => 'select',
        '#title' => t('Display label'),
        '#description' => t('Used to identify the applied tax in order summaries.'),
        '#options' => $this->getDisplayLabels(),
        '#default_value' => $this->configuration['display_label'],
    ];
    $form['round'] = [
        '#type' => 'checkbox',
        '#title' => t('Round tax at the order item level'),
        '#description' => t('Sales taxes are not rounded at the order item level, while VAT-style taxes are rounded.'),
        '#default_value' => $this->configuration['round'],
    ];

    $wrapper_id = Html::getUniqueId('tax-type-ajax-wrapper');
    $form['#prefix'] = '<div id="' . $wrapper_id . '">';
    $form['#suffix'] = '</div>';
    // Ajax callbacks need rates and territories to be in form state.
    if (!$form_state->get('tax_form_initialized')) {
      $rates = $this->configuration['rates'];
      $territories = $this->configuration['territories'];
      // Initialize empty rows in case there's no data yet.
      $rates = $rates ?: [NULL];
      $territories = $territories ?: [NULL];

      $form_state->set('rates', $rates);
      $form_state->set('territories', $territories);
      $form_state->set('tax_form_initialized', TRUE);
    }

    $form['rates'] = [
        '#type' => 'table',
        '#header' => [
            $this->t('Tax rate'),
            $this->t('Percentage'),
            $this->t('Operations'),
        ],
        '#input' => FALSE,
    ];
    foreach ($form_state->get('rates') as $index => $rate) {
      $rate_form = &$form['rates'][$index];
      $rate_form['rate']['id'] = [
          '#type' => 'value',
          '#value' => $rate ? $rate['id'] : $this->uuidGenerator->generate(),
      ];
      $rate_form['rate']['label'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Name'),
          '#default_value' => $rate ? $rate['label'] : '',
          '#maxlength' => 255,
          '#required' => TRUE,
      ];
      $rate_form['percentage'] = [
          '#type' => 'commerce_number',
          '#title' => $this->t('Percentage'),
          '#default_value' => $rate ? $rate['percentage'] * 100 : 0,
          '#field_suffix' => $this->t('%'),
          '#min' => 0,
          '#max' => 100,
      ];
      $rate_form['territory_postal_limits'] = [
          '#title' => 'Postal Code Limits',
          '#type' => 'textarea',
          '#default_value' => $rate ? $rate['territory_postal_limits'] : '',
          '#required' => FALSE,
          '#description' => 'Enter one or more postal codes separated by commas.',
      ];
      $rate_form['remove'] = [
          '#type' => 'submit',
          '#name' => 'remove_rate' . $index,
          '#value' => $this->t('Remove'),
          '#limit_validation_errors' => [],
          '#submit' => [[get_class($this), 'removeRateSubmit']],
          '#rate_index' => $index,
          '#ajax' => [
              'callback' => [get_class($this), 'ajaxCallback'],
              'wrapper' => $wrapper_id,
          ],
      ];
    }
    $form['rates'][] = [
        'add_rate' => [
            '#type' => 'submit',
            '#value' => $this->t('Add rate'),
            '#submit' => [[get_class($this), 'addRateSubmit']],
            '#limit_validation_errors' => [],
            '#ajax' => [
                'callback' => [get_class($this), 'ajaxCallback'],
                'wrapper' => $wrapper_id,
            ],
        ],
    ];

    $form['territories'] = [
        '#type' => 'table',
        '#header' => [
            $this->t('Territory'),
            $this->t('Operations'),
        ],
        '#input' => FALSE,
        '#prefix' => '<p>' . $this->t('The tax type will be used if both the customer and the store belong to one of the territories.') . '</p>',
    ];
    foreach ($form_state->get('territories') as $index => $territory) {
      $territory_form = &$form['territories'][$index];

      $territory_form['territory'] = [
          '#type' => 'address_zone_territory',
          '#default_value' => $territory,
          '#required' => TRUE,
      ];
      $territory_form['remove'] = [
          '#type' => 'submit',
          '#name' => 'remove_territory' . $index,
          '#value' => $this->t('Remove'),
          '#limit_validation_errors' => [],
          '#submit' => [[get_class($this), 'removeTerritorySubmit']],
          '#territory_index' => $index,
          '#ajax' => [
              'callback' => [get_class($this), 'ajaxCallback'],
              'wrapper' => $wrapper_id,
          ],
      ];
    }
    $form['territories'][] = [
        'add_territory' => [
            '#type' => 'submit',
            '#value' => $this->t('Add territory'),
            '#submit' => [[get_class($this), 'addTerritorySubmit']],
            '#limit_validation_errors' => [],
            '#ajax' => [
                'callback' => [get_class($this), 'ajaxCallback'],
                'wrapper' => $wrapper_id,
            ],
        ],
    ];
    unset($form['rates']['1']['add_rate']);
    return $form;
  }

  /**
   * Ajax callback for tax rate and zone territory operations.
   */
  public static function ajaxCallback(array $form, FormStateInterface $form_state) {
    return $form['configuration'];
  }

  /**
   * Submit callback for adding a new rate.
   */
  public static function addRateSubmit(array $form, FormStateInterface $form_state) {
    $rates = $form_state->get('rates');
    $rates[] = [];
    $form_state->set('rates', $rates);
    $form_state->setRebuild();
  }

  /**
   * Submit callback for removing a rate.
   */
  public static function removeRateSubmit(array $form, FormStateInterface $form_state) {
    $rates = $form_state->get('rates');
    $index = $form_state->getTriggeringElement()['#rate_index'];
    unset($rates[$index]);
    $form_state->set('rates', $rates);
    $form_state->setRebuild();
  }

  /**
   * Submit callback for adding a new territory.
   */
  public static function addTerritorySubmit(array $form, FormStateInterface $form_state) {
    $territories = $form_state->get('territories');
    $territories[] = [];
    $form_state->set('territories', $territories);
    $form_state->setRebuild();
  }

  /**
   * Submit callback for removing a territory.
   */
  public static function removeTerritorySubmit(array $form, FormStateInterface $form_state) {
    $territories = $form_state->get('territories');
    $index = $form_state->getTriggeringElement()['#territory_index'];
    unset($territories[$index]);
    $form_state->set('territories', $territories);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    // Filter out the button rows.
    $values['rates'] = array_filter($values['rates'], function ($rate) {
      return !empty($rate) && !isset($rate['add_rate']);
    });
    $values['territories'] = array_filter($values['territories'], function ($territory) {
      return !empty($territory) && !isset($territory['add_territory']);
    });
    $form_state->setValue($form['#parents'], $values);

    if (empty($values['rates'])) {
      $form_state->setError($form['rates'], $this->t('Please add at least one rate.'));
    }
    if (empty($values['territories'])) {
      $form_state->setError($form['territories'], $this->t('Please add at least one territory.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);

      $this->configuration['display_label'] = $values['display_label'];
      $this->configuration['round'] = $values['round'];
      $this->configuration['rates'] = [];
      $this->configuration['territory_postal_limits'] = $values['territory_postal_limits'];

      foreach (array_filter($values['rates']) as $rate) {

        $this->configuration['rates'][] = [
            'id' => $rate['rate']['id'],
            'label' => $rate['rate']['label'],
            'percentage' => $rate['percentage'] / 100,
            'territory_postal_limits' => $rate['territory_postal_limits'],
        ];
      }
      $this->configuration['territories'] = [];
      foreach (array_filter($values['territories']) as $territory) {
        $this->configuration['territories'][] = $territory['territory'];
      }
    }
  }

  /**
   * Gets the available display labels.
   *
   * @return array
   *   The display labels, keyed by machine name.
   */
  protected function getDisplayLabels() {
    return [
        'tax' => $this->t('FL Sales Tax'),
    ];
  }

  /**
   * Gets the configured display label.
   *
   * @return string
   *   The configured display label.
   */
  protected function getDisplayLabel() {
    $display_labels = $this->getDisplayLabels();
    $display_label_id = $this->configuration['display_label'];
    if (isset($display_labels[$display_label_id])) {
      $display_label = $display_labels[$display_label_id];
    }
    else {
      $display_label = reset($display_labels);
    }
    return $display_label;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldRound() {
    return $this->configuration['round'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildZones() {
    $rates = $this->configuration['rates'];
    // The plugin doesn't support defining multiple percentages with own
    // start/end dates for UX reasons, so a start date is invented here.
    foreach ($rates as &$rate) {
      $rate['percentages'][] = [
          'number' => strval($rate['percentage']),
          'start_date' => '2000-01-01',
      ];
      unset($rate['percentage']);
    }
    // The first defined rate is assumed to be the default.
    $rates[0]['default'] = TRUE;

    $zones = [];
    $zones['default'] = new TaxZone([
        'id' => 'default',
        'label' => 'Default',
        'display_label' => $this->getDisplayLabel(),
        'territories' => $this->configuration['territories'],
        'rates' => $rates,
    ]);

    return $zones;
  }

  /**
   * Resolves the tax zones for the given order item and customer profile.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The order item.
   * @param \Drupal\profile\Entity\ProfileInterface $customer_profile
   *   The customer profile. Contains the address and tax number.
   *
   * @return \Drupal\commerce_tax\TaxZone[]
   *   The tax zones.
   */
  protected function resolveZones(OrderItemInterface $order_item, ProfileInterface $customer_profile) {

    //$customer_address = $customer_profile->address->getValue();
    $customer_address = $customer_profile->get('address')->first();
    //dpm($customer_address);
    $resolved_zones = [];


    /*
    if (\Drupal\user\Entity\User::load(\Drupal::currentUser()->id())->hasRole('tax_exempt')){
      return $resolved_zones = [];
    }*/

    foreach ($this->getZones() as $zone) {

      if ($zone->match($customer_address)) {
        // Now limit to postal codes
        $configs = $this->configuration['rates'];
        foreach ($configs as $key => $config) {
          //dpm($config);
          $territory_postal_limits = array_map('trim', explode(',', $config['territory_postal_limits']));

          if (in_array($customer_address->postal_code, $territory_postal_limits)) {
            $resolved_zones[] = $zone;
          }
        }
      }
    }

    return $resolved_zones;
  }
}
