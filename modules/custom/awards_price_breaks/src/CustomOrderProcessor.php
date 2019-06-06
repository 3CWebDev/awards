<?php
namespace Drupal\awards_price_breaks;
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
    $order2 = $order;
    foreach ($order->getItems() as $order_item) {
      $total_quantity = 0;
      $product_variation = $order_item->getPurchasedEntity();
      $sku_main = $product_variation->getSku();
      $current_pricebreak = FALSE;
      foreach ($order2->getItems() as $order_item2) {
        $product_variation = $order_item2->getPurchasedEntity();
        if (isset($product_variation->field_prod_var_price_break) && !$product_variation->field_prod_var_price_break->isEmpty()) {
          $sku = $product_variation->getSku();
          if ($sku == $sku_main) {
            $total_quantity = $total_quantity + $order_item2->quantity->getString();
          }
          foreach ($product_variation->field_prod_var_price_break as $price_break) {
            if ($total_quantity >= intval($price_break->threshold)) {
              if (!isset($current_pricebreak) || $current_pricebreak->threshold < $price_break->threshold) {
                $current_pricebreak = $price_break;
              }
            }
          }
          if ($current_pricebreak) {
            $price = new Price($current_pricebreak->price, 'USD');
            $order_item->setUnitPrice($price);
          }
        }
      }
    }
  }
}