<?php
namespace Drupal\awards_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure awards_custom settings for this site.
 */
class storeSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'awards_custom_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
        'awards_custom.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('awards_custom.settings');

    $form['awards_custom_product_days'] = array(
        '#type' => 'number',
        '#title' => $this->t('Number of product days'),
        '#default_value' => $config->get('awards_custom_product_days'),
        '#required' => TRUE,
    );

    $form['awards_custom_medallion_text_markup'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('The Medallion markup price per item if custom text is selected (example: .75)'),
      '#default_value' => $config->get('awards_custom_medallion_text_markup'),
      '#required' => TRUE,
    );


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration
    $this->configFactory->getEditable('awards_custom.settings')
        // Set the submitted configuration setting

        ->set('awards_custom_product_days', $form_state->getValue('awards_custom_product_days'))
        ->set('awards_custom_medallion_text_markup', $form_state->getValue('awards_custom_medallion_text_markup'))

        // You can set multiple configurations at once by making
        // multiple calls to set()
        //->set('other_things', $form_state->getValue('other_things'))
        ->save();

    parent::submitForm($form, $form_state);
  }
}