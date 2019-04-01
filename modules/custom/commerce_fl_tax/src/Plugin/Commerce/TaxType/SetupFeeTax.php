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
 * Provides the County tax type.
 *
 * @CommerceTaxType(
 *   id = "commerce_fl_tax_setup",
 *   label = "Florida Sales Tax for setup fees",
 * )
 */
class SetupFeeTax extends LocalTaxTypeBase {

  public function applies(OrderInterface $order) {
    //$store = $order->getStore();
    //return $this->matchesAddress($store) || $this->matchesRegistrations($store);
    // TODO:
    return TRUE;
  }


  /**
   * {@inheritdoc}
   */
  public function apply(OrderInterface $order) {

    //dpm($order->collectAdjustments());

    // First obtain current tax rate - if one doesn't exist then exit
    foreach($order->collectAdjustments() as $key => $adjustment){
      if ($adjustment->getType() == 'tax'){
        //dpm($adjustment);

        $percentage = $adjustment->getPercentage();
        $source_id = $adjustment->getSourceId();
        $setup_fees = 0;

        foreach($order->collectAdjustments() as $key => $adjustment){

          // Get the total setup fee charges
          if ($adjustment->getType() == 'custom_adjustment'){
            $setup_fees += $adjustment->getAmount()->getNumber();
          }
        };

        $calculated_tax = $setup_fees * $percentage;

        $tax_amount = new \Drupal\commerce_price\Price((string)$calculated_tax, 'USD');

        $order->addAdjustment(new Adjustment([
          'type' => 'tax',
          'label' => 'FL Sales Tax',
          'amount' => $tax_amount,
          //'percentage' => (string)$percentage,
          'source_id' => $source_id,
          'included' => FALSE,
        ]));

        break;
      }
    }


  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['rates'] = $this->buildRateSummary();
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

}
