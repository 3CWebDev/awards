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
    $entity = $items[0]->getEntity();
    $price_object = $entity->get('price')->getValue();
    //$base_price = round($price_object[0]['number'],2);
    $base_price = number_format($price_object[0]['number'],2);



    foreach ($items as $delta => $item) {
      $first_break = $item->threshold -1;
      $elements[] = [
        '#first' => TRUE,
        '#threshold' => $first_break,
        '#price' => $base_price,
        '#theme' => 'awards_price_breaks_formatter',
      ];
      break;

    }
    foreach ($items as $delta => $item) {
      $elements[$delta + 1] = [
          '#threshold' => $item->threshold,
          '#price' => $item->price,
          '#theme' => 'awards_price_breaks_formatter',
      ];
    }

    foreach ($items as $delta => $item) {
      if (isset($elements[$delta + 2]['#threshold'])){
        $elements[$delta + 1]['#threshold_high'] = $elements[$delta + 2]['#threshold']-1;
      }
    }

    return $elements;
  }

}
