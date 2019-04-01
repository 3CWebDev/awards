<?php

namespace Drupal\commerce_customizations;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Adjustment;

/**
 * Provides an order processor that modifies the cart according to the business logic.
 */
class CustomOrderProcessor implements OrderProcessorInterface
{
  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order)  {
    $skus = array();
    foreach ($order->getItems() as $order_item) {
      // SetAdjustment to empty initially.
      $order_item->setAdjustments([]);
      $product_variation = $order_item->getPurchasedEntity();

      if (!empty($product_variation) && !$product_variation->get('field_setup_fee')->isEmpty()){

        $product_id = $product_variation->get('product_id')->getValue()[0]['target_id'];
        $product = Product::load($product_id);
        $product_type = $product->get('type')->getValue()[0]['target_id'];
        $product_title = $product->getTitle();

        if ($product_type == 'default') {
          $sku = $product_variation->getSku();

          if (!in_array($sku, $skus)){

            $skus[] = $sku;
            $new_adjustment = $product_variation->get('field_setup_fee')->getString();

            $adjustments = $order_item->getAdjustments();
            // Apply custom adjustment.
            $adjustments[] = new Adjustment([
              'type' => 'custom_adjustment',
              'label' => 'Setup Fee - ' . $product_title,
              'amount' => new Price('+' . $new_adjustment, 'USD'),
              'included' => FALSE,
            ]);
            $order_item->setAdjustments($adjustments);
            $order_item->save();
          }

        }

      }

    }
  }
}