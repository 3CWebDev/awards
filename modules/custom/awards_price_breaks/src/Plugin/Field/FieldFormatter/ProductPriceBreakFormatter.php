<?php

namespace Drupal\awards_price_breaks\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'product_price_break_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "product_price_break_formatter",
 *   label = @Translation("Product price break formatter"),
 *   field_types = {
 *     "product_price_break_item"
 *   }
 * )
 */
class ProductPriceBreakFormatter extends FormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
          '#threshold' => $item->threshold,
          '#price' => $item->price,
          '#theme' => 'awards_price_breaks_formatter',
      ];
    }
    return $elements;
  }

}
