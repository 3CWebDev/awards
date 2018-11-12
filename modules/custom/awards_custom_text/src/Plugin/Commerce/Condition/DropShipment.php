<?php

namespace Drupal\awards_custom_text\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the customer approved condition for purchase orders.
 *
 * @CommerceCondition(
 *   id = "drop_shipment",
 *   label = @Translation("Is Drop Shipment"),
 *   display_label = @Translation("Limit by Drop Shipment"),
 *   category = @Translation("Order"),
 *   entity_type = "commerce_order",
 * )
 */
class DropShipment extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
            'operator' => 'equals',
        ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $options = array(
        'equals' => 'Order contains drop ship item',
        'notequals' => 'Order does not contain a drop ship item',
    );

    $form['operator'] = [
        '#type' => 'select',
        '#title' => t('Operator'),
        '#options' => $options,
        '#default_value' => $this->configuration['operator'],
        '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['operator'] = $values['operator'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;

    $items = $order->getItems();
    foreach ($items as $order_item){
      $product_variation = $order_item->getPurchasedEntity();

      $drop_ship = $product_variation->field_prod_is_drop_ship_item->getString();
    }
dpm($drop_ship);
    switch ($this->configuration['operator']) {
      case 'equals':
        if ($drop_ship == 1){
          return TRUE;
        }else{
          return FALSE;
        }
      case 'notequals':
        if ($drop_ship == 1){
          return FALSE;
        }else{
          return TRUE;
        }
    }
  }

}
