<?php

namespace Drupal\commerce_fl_tax\Plugin\Commerce\TaxType;

use Drupal\commerce_tax\TaxZone;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_tax\Plugin\Commerce\TaxType\LocalTaxTypeBase;
use CommerceGuys\Addressing\Address;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\commerce_tax\Resolver\ChainTaxRateResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\profile\Entity\ProfileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Provides the pickup tax type.
 *
 * @CommerceTaxType(
 *   id = "commerce_fl_tax_pickp",
 *   label = "Florida Sales Tax for pickup orders",
 * )
 */
class PickupTax extends LocalTaxTypeBase {

  public function applies(OrderInterface $order) {
    // 4 & 5

    foreach ($order->shipments as $reference) {

      $method_id = $reference->entity->getShippingMethodId();

      // 4 & 5 are the method ID of the two local pickup types
      if ($method_id == 4 || $method_id == 5){
        return TRUE;
      }

    }

  }


  /**
   * {@inheritdoc}
   */
  public function apply(OrderInterface $order) {

    // First remove any existing taxes

    foreach($order->collectAdjustments() as $key => $adjustment) {
      if ($adjustment->getType() == 'tax') {
        //dpm($adjustment);
        $order->removeAdjustment($adjustment);
      }
    }

    $subtotal = $order->getSubtotalPrice();
    $setup_fees = 0;

    foreach($order->collectAdjustments() as $key => $adjustment){

      // Get the total setup fee charges
      if ($adjustment->getType() == 'custom_adjustment'){
        $setup_fees += $adjustment->getAmount()->getNumber();
      }
    };
    $order_total = $subtotal->getNumber() + $setup_fees;

    //$percentage = .075; // hard coded tax rate of store location
    $percentage = $this->configuration['percentage'] * .01;
    
    $calculated_tax = $order_total * $percentage;

    $tax_amount = new \Drupal\commerce_price\Price((string)$calculated_tax, 'USD');

    $source_id = 'florida_sales_tax_7_5_';

    $order->addAdjustment(new Adjustment([
      'type' => 'tax',
      'label' => 'FL Sales Tax',
      'amount' => $tax_amount,
      //'percentage' => (string)$percentage,
      'source_id' => $source_id,
      'included' => FALSE,
    ]));

  }



  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['percentage'] = [
      '#type' => 'commerce_number',
      '#title' => $this->t('Percentage'),
      '#default_value' => $this->configuration['percentage'],
      '#field_suffix' => $this->t('%'),
      '#min' => 0,
      '#max' => 100,
    ];

    // Replace the phrase "tax rates" with "rates" to be more precise.
    $form['rates']['#markup'] = $this->t('The following rates are provided:');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildZones() {
    $zones = [];


    return $zones;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['percentage'] = $values['percentage'];

    }

  }

}


