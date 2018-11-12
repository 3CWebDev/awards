<?php

namespace Drupal\awards_custom_text\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @CommerceCheckoutPane(
 *  id = "custom_order_validation",
 *  label = @Translation("Custom order validation"),
 *  admin_label = @Translation("Custom order validation"),
 * )
 */

class CustomTextCheckoutPane extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    $order_items = $this->order->order_items->getValue();

    foreach ($order_items as $order_item){

      $order_item = \Drupal\commerce_order\Entity\OrderItem::load($order_item['target_id']);

      if ($order_item){
        if ($order_item->field_repeat_order->getString()){
          return FALSE;
        }
        $product_variation = $order_item->getPurchasedEntity();
        $product_id = $product_variation->product_id->getString();
        $product = \Drupal\commerce_product\Entity\Product::load($product_id);
        if ($product->hasField('field_number_of_lines') && is_numeric($product->field_number_of_lines->getString()) && $product->field_number_of_lines->getString() > 0){
          if (isset($order_item->field_item_custom_text) && $order_item->field_custom_text_entered->getString() != 1){
            return TRUE;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {

    // Redirect to cart
    drupal_set_message($this->t('@product-title needs custom engraving text. Please review item.', [
        '@product-title' => 'An item in your cart',
    ]), 'error', TRUE);

    $path = '/cart';
    $response = new RedirectResponse($path, 302);
    $response->send();
    exit();
  }

}