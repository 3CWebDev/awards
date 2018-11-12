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
    if (isset($entity->field_prod_var_price_break) && !$entity->field_prod_var_price_break->isEmpty()) {
      foreach ($entity->field_prod_var_price_break as $price_break) {
        if ($quantity >= intval($price_break->threshold)) {
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
