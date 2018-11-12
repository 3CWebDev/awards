<?php

namespace Drupal\awards_mix_price_breaks\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'product_price_break_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "product_mix_price_break_formatter",
 *   label = @Translation("Product mix price break formatter"),
 *   field_types = {
 *     "product_mix_price_break_item"
 *   }
 * )
 */
class ProductMixPriceBreakFormatter extends FormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
          '#threshold' => $item->threshold,
          '#price' => $item->price,
          '#sku' => $item->sku,
          '#theme' => 'awards_mix_price_breaks_formatter',
      ];
    }
    return $elements;
  }

}
