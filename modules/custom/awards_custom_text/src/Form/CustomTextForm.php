<?php

/**
 * @file
 * Contains \Drupal\awards_custom_text\CustomTextForm.
 */

namespace Drupal\awards_custom_text\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
//use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Contribute form.
 */
class CustomTextForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'awards_custom_text_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $product_id = NULL, $order_item_id = NULL) {

    $product = \Drupal\commerce_product\Entity\Product::load($product_id);

    $categories = $product->field_prod_category->getValue(0);
    $category_classes = array();
    foreach ($categories as $key => $category){
      $term = \Drupal\taxonomy\Entity\Term::load($category['target_id'])->name->value;
      $category_classes[] = preg_replace('@[^a-z0-9-]+@','-', strtolower($term));
    }

    $num_lines = $product->field_number_of_lines->getString();
    $order_item = \Drupal\commerce_order\Entity\OrderItem::load($order_item_id);

    // Check product
    if (!is_numeric($num_lines)){
      $form['markup'] = array(
          '#markup' => 'Product Not Customizable.',
      );
      return $form;
    }

    // Check that order item exists
    if (!is_object ($order_item) || !is_numeric($order_item->order_id->getString())){
      $form['markup'] = array(
          '#markup' => 'Nothing to customize.',
      );
      return $form;
    }

    // Check permissions
    $order = \Drupal\commerce_order\Entity\Order::load($order_item->order_id->getString());
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    if ($order->uid->getString() != $user->id){
      if (!$order_item || !is_numeric($order_item->order_id->getString())){
        $form['markup'] = array(
            '#markup' => 'Not allowed.',
        );
        return $form;
      }

    }

    $custom_text_entered = $order_item->field_custom_text_entered->getString();

    if ($custom_text_entered){
      // Load Custom Text 'Paragraphs'
      $target_ids = $order_item->get('field_item_custom_text')->getValue();

      foreach ($target_ids as $key => $target_id){
        $paragraphs[$key+1] = \Drupal\paragraphs\Entity\Paragraph::load($target_id['target_id']);
      }
    }

    $qty = $order_item->quantity->getString();

    $form['#cache']['tags'] = $order_item->getCacheTags();

    $form['order_item_id'] = array(
        '#type' => 'hidden',
        '#value' => $order_item_id,
    );
    $form['qty'] = array(
        '#type' => 'hidden',
        '#value' => $qty,
    );
    $form['num_lines'] = array(
        '#type' => 'hidden',
        '#value' => $num_lines,
    );
    $form['#attached']['library'][] = 'awards_custom_text/awards_custom_text'; // Custom JS
    $form['#attached']['drupalSettings']['awards_custom']['awards_custom'] ['qty'] = $qty; // send variable to JS

    $options = array(
        1 => 'New Order',
        2 => "Repeat Order",
    );
    $form['order_type'] = array(
        '#type' => 'radios',
        '#title' => 'Order Type',
        '#options' => $options,
        '#weight' => -2,
        '#default_value' => ($order_item->field_repeat_order->getString() ? $order_item->field_repeat_order->getString() : 1),
    );

    $form['repeat_order_info'] = array(
        '#type' => 'textarea',
        '#title' => 'Repeat Order Information',
        '#description' => '<p>We will use your most recent design layout. Please provide any names, dates or other information that should change from the previous layout.</p>',
        '#default_value' => $order_item->field_repeat_order_description->getString(),
        '#states' => array(
            'visible' => array(
              ':input[name="order_type"]' => array('value' => '2'),
            ),
            'required' => array(
                ':input[name="order_type"]' => array('value' => '2'),
            ),
        ),

    );

    for ($x = 1; $x <= $qty; $x++) {

      $form[$x] = array(
          '#type' => 'fieldset',
          '#title' => t('Item ' . $x),
          '#collapsible' => FALSE,
          '#collapsed' => FALSE,
          '#attributes' => array(
              'class' => array('container-inline', 'custom-text-group'),
          ),
        '#states' => array('visible' => array(
            ':input[name="order_type"]' => array('value' => '1'),
        )),
      );

      $form[$x]['text_preview'] = array(
          '#markup' => '',
          '#prefix' => '<div id="text_preview' . $x . '"><div class="' . implode(' ', $category_classes) .'">',
          '#suffix' => '</div></div>',
      );

      // Text Template Options
      $categories = $product->field_prod_category->getValue();
      if (isset($categories[0])){

        $options = awards_custom_text_get_templates($categories, $num_lines);
        if (count($options) > 1){
          $form['template'] = array(
              '#type' => 'select',
              '#title' => 'Template Examples',
              '#options' => $options,
              '#weight' => -1,
              '#states' => array(
                  'visible' => array(
                      ':input[name="order_type"]' => array('value' => '1'),
                  ),
              ),

          );
        }
      }

      $form[$x]["#tree"] = TRUE;

      if ($custom_text_entered && $paragraphs[$x]->field_prod_engraving_text->getValue()){
        $default_values = $paragraphs[$x]->field_prod_engraving_text->getValue();
      }

      for ($y = 1; $y <= $num_lines; $y++) {
        $form[$x][$y]['text'] = array(
            '#type' => 'textfield',
            '#title' => t('line ' . $y),
            '#required' => FALSE,
            '#attributes' => array(
                'item' => $x,
                'line' => $y,
                'placeholder' => t('Type text here'),
            ),
        );

        if ($qty >1 && $x == 1){
          $form[$x][$y]['copy'] = array(
              '#type' => 'checkbox',
              '#title' => 'repeat',
              '#attributes' => array(
                  'item' => $x,
                  'line' => $y,
              ),
          );
        }


        if ($custom_text_entered && $default_values[$y-1]['value'] != '<br />'){
          $form[$x][$y]['text']['#default_value'] = $default_values[$y-1]['value'];
        }
      }
    }

    $form['agree'] = array(
        '#type' => 'checkbox',
        '#title' => '<p>YES, I HAVE REVIEWED THE SPELLING.</p>',
        '#description' => '<p>Engraving Will Be Centered On Award.</p><p>Awards4U\'s engraving experts will personalize the award with care.</p><p>It will look as good, or better, than the preview.
                            Please note: We cannot engrave emojis.</p>',
    );

    $form['cancel'] = array(
        '#type' => 'submit',
        '#value' => t('Cancel & Return to Product Page'),
        '#weight' => 50,
        //'#limit_validation_errors' => array(),
        '#submit' => array('::submitFormCancel'),
        '#attributes' => array('onclick' => 'if(!confirm("Are you sure you want to leave this page? Any customizations entered will be lost.")){return false;}'),
    );

    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit'),
        '#weight' => 50,
        '#submit' => array('::submitForm'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    if ($trigger['#submit'][0] == '::submitForm'){
      $values = $form_state->getValues();

      if (!$values['agree']){
        $form_state->setErrorByName('agree', t('You must agree to the terms before continuing.'));
      }
      if ($values['order_type'] == 1){
        $qty = $values['qty'];
        $num_lines = $values['num_lines'];
        for ($x = 1; $x <= $qty; $x++) {
          $empty = TRUE;
          for ($y = 1; $y <= $num_lines; $y++) {
            if (!empty($values[$x][$y]['text'])){
              $empty = FALSE;
              break;
            }
          }
          if ($empty){
            $form_state->setErrorByName($x . '][1][text', t('Item #' . $x . ' needs custom text entered.'));
          }
        }
      }

    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitFormCancel(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    if (isset($values['order_item_id'])){
      $order_item_id = $values['order_item_id'];
      $order_item = \Drupal\commerce_order\Entity\OrderItem::load($order_item_id);

      $product_variation = $order_item->getPurchasedEntity();
      $product_id = $product_variation->product_id->getString();
      $order_item->delete();
    }

    //$response = new RedirectResponse('/cart', 302);
    $response = new RedirectResponse('/product/' . $product_id, 302);
    $response->send();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save values to order item and update
    $values = $form_state->getValues();


    $order_item_id = $values['order_item_id'];
    $order_item = \Drupal\commerce_order\Entity\OrderItem::load($order_item_id);

    // New order type
    $order_item->set('field_repeat_order', $values['order_type']); // Set the value on the order item

    if ($values['order_type'] == 1){
      // Save the form values to 'Paragraph' entities and save to the order_item
      $is_new = !$order_item->get('field_custom_text_entered')->getString();
      awards_custom_text_create_paragraph($order_item, $values, $is_new);

      $order_item->set('field_custom_text_entered', 1); // mark the order_item object as text being entered

    }else{
      // Repeat order type
      $order_item->set('field_repeat_order_description', $values['repeat_order_info']);
    }

    $order_item->save();
    $response = new RedirectResponse('/cart', 302);
    $response->send();
  }
}

function awards_custom_text_get_templates($categories, $num_lines){
  $options = array();
  $options[0] = '-select a template-';

  foreach ($categories as $key => $category){
    $target_id = $category['target_id'];

    // $term = \Drupal\taxonomy\Entity\Term::load($target_id);

    // Find Templates with matching categories
    $templates = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
        'field_template_product_category' => $target_id,
    ]);

    foreach($templates as $template){

      $target_ids = $template->field_template_text->getValue();
      foreach($target_ids as $key => $target_id){

        $paragraph = \Drupal\paragraphs\Entity\Paragraph::load($target_id['target_id']);
        $count = count($paragraph->get('field_template_engraving_text')->getValue());
        if ($count <= $num_lines){
          $options[$target_id['target_id']] = $template->name->getString();
        }
      }
    }
  }

  return $options;
}