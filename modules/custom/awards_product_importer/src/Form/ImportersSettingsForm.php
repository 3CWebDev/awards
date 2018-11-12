<?php

namespace Drupal\awards_product_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ImportersSettingsForm.
 *
 * @package Drupal\awards_importers\Form
 *
 * @ingroup awards_importers
 */
class ImportersSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'Importers_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
    $config = \Drupal::service('config.factory')->getEditable('awards_importers.settings');
    $config->set('integration_path', $form_state->getValue('integration_path'))->save();
    $config->set('products_integration_file', $form_state->getValue('products_integration_file'))->save();
    $config->set('categories_integration_file', $form_state->getValue('categories_integration_file'))->save();
  }

  /**
   * Defines the settings form for Order Ship entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['Importers_settings']['#markup'] = 'Settings form for Paycheck Exchange Catalog Imports.';

    $config = \Drupal::service('config.factory')->getEditable('awards_importers.settings');

    $integration_path = $config->get('integration_path');
    $form['Importers_settings']['integration_path'] = array(
     '#type' => 'textfield',
     '#title' => t('Integration Path'),
     '#default_value' => $integration_path ?: "integrations/import/catalog",
     '#description' => "Path in which to search for product and catalog files. Omit leading slash.",
     '#size' => 100,
     '#maxlength' => 255
    );

    $products_integration_file = $config->get('products_integration_file');
    $form['Importers_settings']['products_integration_file'] = array(
     '#type' => 'textfield',
     '#title' => t('Products Filename'),
     '#default_value' => $products_integration_file ?: "products.csv",
     '#size' => 100,
     '#maxlength' => 255
    );

    $categories_integration_file = $config->get('categories_integration_file');
    $form['Importers_settings']['categories_integration_file'] = array(
     '#type' => 'textfield',
     '#title' => t('Categories Filename'),
     '#default_value' => $categories_integration_file ?: "categories.csv",
     '#size' => 100,
     '#maxlength' => 255
    );

    $form['Importers_settings']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

}
