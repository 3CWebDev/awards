<?php

namespace Drupal\awards_custom_text\Plugin\Commerce\CheckoutFlow;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowWithPanesBase;
// use Drupal\Core\Form\FormStateInterface;

/**
 * @CommerceCheckoutFlow(
 *  id = "custom_checkout_flow",
 *  label = @Translation("Custom checkout flow"),
 * )
 */
class CustomTextCheckoutFlow extends CheckoutFlowWithPanesBase {


  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    // Note that previous_label and next_label are not the labels
    // shown on the step itself. Instead, they are the labels shown
    // when going back to the step, or proceeding to the step.
    return [
        'validate' => [
            'label' => $this->t('Validate Order'),
            'previous_label' => $this->t('Go back'),
            //'next_label' => $this->t('Proceed to checkout'),
        ],
        'login' => [
            'label' => $this->t('Login'),
            'previous_label' => $this->t('Go back'),
            'has_sidebar' => FALSE,
        ],
        'order_information' => [
            'label' => $this->t('Order information'),
            'has_sidebar' => TRUE,
            'previous_label' => $this->t('Go back'),
        ],
        'review' => [
            'label' => $this->t('Review'),
            'next_label' => $this->t('Continue to review'),
            'previous_label' => $this->t('Go back'),
            'has_sidebar' => TRUE,
        ],
    ] + parent::getSteps();
  }

}