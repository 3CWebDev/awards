<?php

namespace Drupal\awards_tax_exempt\Resolver;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_tax\Resolver\TaxRateResolverInterface;
use Drupal\commerce_tax\TaxZone;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\commerce_tax\TaxRate;

/**
 * Returns the tax zone's default tax rate.
 */
class ExemptTaxResolver implements TaxRateResolverInterface
{

  /**
   * {@inheritdoc}
   */
  public function resolve(TaxZone $zone, OrderItemInterface $order_item, ProfileInterface $customer_profile)
  {
    // commerce_shipment
    // 4 & 5
    $order = \Drupal\commerce_order\Entity\Order::load($order_item->getOrderId());
    //$shipments = $order->get('shipments')->getValue();

    $rate = new TaxRate(
      ['id' => 'pst',
        'label' => 'PST',
        'percentages' => [
          ['number' => '0.07', 'start_date' => '2013-04-01'],
        ],
        'default' => TRUE,
      ]
    );
    //dpm($rate);
    return $rate;

    foreach ($order->shipments as $reference) {

      $method_id = $reference->entity->getShippingMethodId();
      $rate = new TaxRate(
        ['id' => 'pst',
          'label' => 'PST',
          'percentages' => [
            ['number' => '0.07', 'start_date' => '2013-04-01'],
          ],
          'default' => TRUE,
        ]
      );
      //dpm($rate);

      return $rate;


      // If no rate has been found, let's others resolvers try to get it.
      if (\Drupal\user\Entity\User::load(\Drupal::currentUser()->id())->hasRole('tax_exempt')){
        return TaxRateResolverInterface::NO_APPLICABLE_TAX_RATE;
      }

    }
  }
}