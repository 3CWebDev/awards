<?php

namespace Drupal\awards_price_breaks\Resolvers;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Resolver\PriceResolverInterface;
use Drupal\commerce_price\Price;

/**
 * Class QtyPriceResolver.
 */
class QtyPriceResolver implements PriceResolverInterface {

  public function resolve(PurchasableEntityInterface $entity, $quantity, Context $context) {

    $store_id = 1;
    $order_type = 'default';
    //$cart_manager = \Drupal::service('commerce_cart.cart_manager');
    $cart_provider = \Drupal::service('commerce_cart.cart_provider');
    $entity_manager = \Drupal::entityManager();
    $store = $entity_manager->getStorage('commerce_store')->load($store_id);
    $cart = $cart_provider->getCart($order_type, $store);

    if (!$cart){
      return;
    }

    $items = $cart->getItems();

    $total_quantity = 0;
    $sku_main = $entity->getSku();


    if (isset($entity->field_prod_var_price_break) && !$entity->field_prod_var_price_break->isEmpty()) {

      foreach ($items as $item){
        $product_variation = $item->getPurchasedEntity();
        $sku = $product_variation->getSku();
        if ($sku == $sku_main){
          $total_quantity = $total_quantity + $item->quantity->getString();
        }
      }

      foreach ($entity->field_prod_var_price_break as $price_break) {
        if ($total_quantity >= intval($price_break->threshold)) {
          if (!isset($current_pricebreak) || $current_pricebreak->threshold < $price_break->threshold) {
            $current_pricebreak = $price_break;
          }
        }
      }

      if (isset($current_pricebreak)) {
        return new Price($current_pricebreak->price, 'USD');
      }
    }


  }


}
