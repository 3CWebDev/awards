<?php

namespace Drupal\awards_mix_price_breaks\Resolvers;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Resolver\PriceResolverInterface;
use Drupal\commerce_price\Price;

/**
 * Class QtyMixPriceResolver.
 */
class QtyMixPriceResolver implements PriceResolverInterface {

  public function resolve(PurchasableEntityInterface $entity, $quantity, Context $context) {

    if (isset($entity->field_mix_match_items) && !$entity->field_mix_match_items->isEmpty()) {

      foreach ($entity->field_mix_match_items as $price_break) {

        // Cycle through cart items to find any matching mix/match SKU to get total qty for discounts
        $cs = \Drupal::service('commerce_store.current_store');
        $cpi = \Drupal::service('commerce_cart.cart_provider');
        $cart = $cpi->getCart('default', $cs->getStore());
        $items = $cart->getItems();
        $total_quantity = 0;
        foreach ($items as $item){
          $product_variation = $item->getPurchasedEntity();
          $sku = $product_variation->getSku();
          if (substr( $sku, 0, strlen($price_break->sku) ) === $price_break->sku){
            $total_quantity += $item->quantity->getString();
          }
       }


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
