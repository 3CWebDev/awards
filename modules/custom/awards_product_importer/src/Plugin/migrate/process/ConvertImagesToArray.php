<?php


/**
 * @file
 * Contains \Drupal\awards_importers\Plugin\migrate\source\ConvertImagesToArray.
 */

namespace Drupal\awards_product_importer\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Photos Entities
 *
 * @MigrateProcessPlugin(
 *   id = "convert_images_to_array"
 * )
 * @code
 * field_text:
 *   plugin: convert_images_to_array
 *   source: Product Image
 * @endcode
 */
class ConvertImagesToArray extends ProcessPluginBase{

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property){

    // Retrieve all images

    $values = explode('|', $value);
    $title = $row->getSourceProperty('Product Name');
    $images = array();

    foreach ($values as $key => $value) {
      $images[$key]['source_path'] = 'public://import/catalog_images/' . $value;
      $images[$key]['destination_path'] = 'public://product_images/' . $value;
      $images[$key]['alt'] = $title;
      $images[$key]['title'] = $title;
    }

    return $images;

  }
}