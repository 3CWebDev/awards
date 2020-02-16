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
    \Drupal::logger('$value')->notice(print_r($value,1));
    // Retrieve all images

    $values = explode('|', $value);
    \Drupal::logger('$values')->notice('<pre>' . print_r($values,1) . '</pre>');

    $images = array();

    // Create an array with all datas
    foreach ($values as $key => $value) {
      \Drupal::logger($key)->notice($value);
      //$date = date('Y-m');
      $filename = $key;
      // Clean a string for use in URL
      //$filename_sanitized = \Drupal::service('pathauto.alias_cleaner')->cleanString($filename);
      $images[$key]['source_path'] = 'public://import/catalog_images/' . $value;
      $images[$key]['destination_path'] = 'public://product_images/' . $value;
      $images[$key]['alt'] = 'Alt Lorem ipsum';
      $images[$key]['title'] = 'Title Lorem ipsum';
    }

    \Drupal::logger('my_module1')->notice('<pre>' . print_r($images,1) . '</pre>');
    // Return value.
    return $images;

  }
}