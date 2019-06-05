<?php

namespace Drupal\awards_markups;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Adjustment;

/**
 * Provides an order processor that modifies the cart according to the business logic.
 */
class MarkupsOrderProcessor implements OrderProcessorInterface
{
  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order)  {


      // The text type is not "No Text"
      foreach ($order->getItems() as $order_item) {

        $text_type = $order_item->field_line_item_text_type->getValue(0);

        if ($text_type[0]['value'] != 3 && $text_type[0]['value'] != 0){

          $product_variation = $order_item->getPurchasedEntity();

          $product = \Drupal\commerce_product\Entity\Product::load($product_variation->getProductId());
          $categories = $product->field_prod_category->getValue(0);
          foreach ($categories as $key => $category) {

            // Does this product belong to the medallion category?
            if ($category['target_id'] == 408 || $category['target_id'] == 619) {
              //This is a Medallion with custom text - let's markup the price now

              $price = $order_item->getUnitPrice()->getNumber();
              $price = $price + .75;


              $price = new Price(strval($price), 'USD');

              $order_item->setUnitPrice($price);

            }
          }

        }

      }


  }
}