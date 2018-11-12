<?php

namespace Drupal\awards_custom_text\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @CommerceCheckoutPane(
 *  id = "extra_info_checkout_pane",
 *  label = @Translation("Additional Information"),
 *  admin_label = @Translation("Extra Info Checkout Pane"),
 * )
 */

class ExtraInfoCheckoutPane extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    $summary = [];
    if ($this->isVisible()) {
      $summary = $this->order->get('field_order_special_instructions')->getString();
    }
    return $summary;
  }
  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {

    $order_extra_info = $this->order->get('field_order_special_instructions')->getString();
    $pane_form['order_extra_info'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Special Instructions'),
        '#default_value' => (isset($order_extra_info) ? $order_extra_info : ''),
        '#required' => FALSE,
        '#description' => '<p>Please enter any special instructions about your order; if this is a repeat order, please note any changes from the previous order that may apply this time.</p>',
    ];
    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $order_extra_info = $values['order_extra_info'];
    $this->order->set('field_order_special_instructions', $order_extra_info);
  }



}