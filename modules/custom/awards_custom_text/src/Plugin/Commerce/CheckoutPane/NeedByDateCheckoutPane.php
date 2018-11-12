<?php

namespace Drupal\awards_custom_text\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @CommerceCheckoutPane(
 *  id = "need_by_date_checkout_pane",
 *  label = @Translation("Need by Date"),
 *  admin_label = @Translation("Order Need by Date"),
 * )
 */

class NeedByDateCheckoutPane extends CheckoutPaneBase implements CheckoutPaneInterface {

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
      $summary = $this->order->get('field_order_date_needed')->getString();
    }
    return $summary;
  }
  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $entity = \Drupal::service('entity_type.manager')->getStorage('commerce_order')->create(array(
            'type' => 'default'
        )
    );

    //Get the EntityFormDisplay (i.e. the default Form Display) of this content type
    $entity_form_display = \Drupal::service('entity_type.manager')->getStorage('entity_form_display')
        ->load('commerce_order.default.default');

    //Get the body field widget and add it to the form
    if ($widget = $entity_form_display->getRenderer('field_order_date_needed')) { //Returns the widget class
      $items = $entity->get('field_order_date_needed'); //Returns the FieldItemsList interface
      $items->filterEmptyItems();

      $pane_form['order_date_needed'] = $widget->form($items, $pane_form, $form_state); //Builds the widget form and attach it to your form
      $pane_form['order_date_needed']['#access'] = $items->access('edit');
      $pane_form['order_date_needed']['#description'] = '<p><strong>NOTE:</strong> All orders require a minimum of 5 days for production and shipping, except "Ship Today" items.</p>';
      //$order_date_needed = $this->order->get('field_order_date_needed')->getString();
      //$pane_form['order_date_needed']['#default_value'] = $order_date_needed;
    }

    $order_date_needed = $this->order->get('field_order_date_needed')->getString();

    $pane_form['order_date_needed']['widget'][0]['value']['#required'] = TRUE;
    $pane_form['order_date_needed']['widget'][0]['value']['#default_value'] = (isset($order_date_needed) ? $order_date_needed : '');

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    
    $values = $form_state->getValue($pane_form['#parents']);

    $order_date_needed = $values['field_order_date_needed'][0]['value'];
    $number_of_working_days = calc_number_of_working_days(date('Y-m-d'), $order_date_needed);

    if ($number_of_working_days <= 2){
      $form_state->setErrorByName('order_date_needed', t('Production time is normally 5 days. Your requested date requires expedited service. Please contact us if you need it before then.'));
    }

  }



  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {

    $values = $form_state->getValue($pane_form['#parents']);
    $order_date_needed =$values['field_order_date_needed'][0]['value'];

    $this->order->set('field_order_date_needed', $order_date_needed);
  }



}


