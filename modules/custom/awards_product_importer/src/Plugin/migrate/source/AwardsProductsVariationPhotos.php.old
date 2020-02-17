<?php

/**
 * @file
 * Contains \Drupal\awards_importers\Plugin\migrate\source\awardsProducts.
 */

namespace Drupal\awards_product_importer\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\migrate\Row;
//use Drupal\taxonomy\Entity\Term;
//use Drupal\commerce_price\Price;

/**
 * Source plugin for Photos Entities
 *
 * @MigrateSource(
 *   id = "awards_products_variation_photos"
 * )
 */
class AwardsProductsVariationPhotos extends CSV{

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row){

    $images = explode('|', $row->getSourceProperty('Product Image'));
    foreach ($images as $image){
      $images_new[]  = $image;
    }
    \Drupal::logger('my_module')->notice(print_r($images_new,1));
    $row->setSourceProperty('Product Image', $images_new);

  }

}

