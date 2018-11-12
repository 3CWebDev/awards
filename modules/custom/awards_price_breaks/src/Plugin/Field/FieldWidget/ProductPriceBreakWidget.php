<?php

namespace Drupal\awards_price_breaks\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'product_price_break_widget' widget.
 *
 * @FieldWidget(
 *   id = "product_price_break_widget",
 *   label = @Translation("Product price break widget"),
 *   field_types = {
 *     "product_price_break_item"
 *   }
 * )
 */
class ProductPriceBreakWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += [
        '#type' => 'fieldset',
    ];
    $element['threshold'] = [
        '#type' => 'number',
        '#title' => t('Threshold'),
        '#default_value' => isset($items[$delta]->threshold) ? $items[$delta]->threshold : NULL,
        '#size' => 10,
        '#placeholder' => $this->getSetting('placeholder'),
        '#min' => 0,
        '#step' => 1,
        '#suffix' => ' pieces',
    ];
    $element['price'] = [
        '#type' => 'textfield',
        '#title' => t('Price'),
        '#default_value' => isset($items[$delta]->price) ? $items[$delta]->price : NULL,
        '#size' => 10,
        '#placeholder' => $this->getSetting('placeholder'),
        '#maxlength' => 20,
        '#prefix' => '$',
    ];
    return $element;
  }

}
