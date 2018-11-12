<?php

namespace Drupal\awards_price_breaks\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'product_price_break_item' field type.
 *
 * @FieldType(
 *   id = "product_price_break_item",
 *   label = @Translation("Product price break item"),
 *   description = @Translation("Price Break Field"),
 *   default_widget = "product_price_break_widget",
 *   default_formatter = "product_price_break_formatter"
 * )
 */
class ProductPriceBreakItem extends FieldItemBase {


  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['threshold'] = DataDefinition::create('integer')
        ->setLabel(new TranslatableMarkup('Threshold'));
    $properties['price'] = DataDefinition::create('string')
        ->setLabel(new TranslatableMarkup('Price'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
        'columns' => [
            'threshold' => [
                'type' => 'int',
            ],
            'price' => [
                'type' => 'numeric',
                'precision' => 19,
                'scale' => 2,
            ],
        ],
    ];
    return $schema;
  }


  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['threshold'] = rand(1,999999);
    $values['price'] = rand(10,10000) / 100;
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('threshold')->getValue();
    return $value === NULL || $value === "" || $value === 0;
  }

}
