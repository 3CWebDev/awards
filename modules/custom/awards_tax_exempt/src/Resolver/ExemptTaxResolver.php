<?php

namespace Drupal\awards_tax_exempt\Resolver;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_tax\Resolver\TaxRateResolverInterface;
use Drupal\commerce_tax\TaxZone;
use Drupal\profile\Entity\ProfileInterface;


/**
 * Returns the tax zone's default tax rate.
 */
class ExemptTaxResolver implements TaxRateResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve(TaxZone $zone, OrderItemInterface $order_item, ProfileInterface $customer_profile) {

    // If no rate has been found, let's others resolvers try to get it.
    if (\Drupal\user\Entity\User::load(\Drupal::currentUser()->id())->hasRole('tax_exempt')){
      return TaxRateResolverInterface::NO_APPLICABLE_TAX_RATE;
    }

  }

}