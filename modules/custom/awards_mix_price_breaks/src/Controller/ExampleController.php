<?php
namespace Drupal\awards_mix_price_breaks\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class ExampleController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myPage() {

    // Cycle through cart items to find skus
    $skus = array();
    $cs = \Drupal::service('commerce_store.current_store');
    $cpi = \Drupal::service('commerce_cart.cart_provider');
    $cart = $cpi->getCart('default', $cs->getStore());
    $items = $cart->getItems();
    $total_quantity = 0;
    foreach ($items as $item){
      $product_variation = $item->getPurchasedEntity();
      $sku = $product_variation->getSku();
      dpm($sku);
      $search = 'P3';
      if (substr( $sku, 0, strlen($search) ) === $search){
        $total_quantity += $item->quantity->getString();
      }
    }

    dpm($total_quantity);
    $element = array(
      '#markup' => 'Hello, world',
    );
    return $element;
  }

}