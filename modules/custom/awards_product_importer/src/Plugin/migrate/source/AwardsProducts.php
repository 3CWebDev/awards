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
 * Source plugin for Product Entities
 *
 * @MigrateSource(
 *   id = "awards_products"
 * )
 */
class AwardsProducts extends CSV{

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row)
  {

    /*
    $variant_sku = $row->getSourceProperty('Variant Sku');
    $sku_prefix = $row->getSourceProperty('Product SKU');
    $row->setDestinationProperty('CombinedSku', $sku_prefix . $variant_sku);
    */
    $sku_prefix = $row->getSourceProperty('SKU');

    $description = $row->getSourceProperty('Product Description');
    $description = strlen($description) > 255 ? substr($description, 0, 50) . "..." : $description;
    $description = filter_var($description);
    $clearstring = filter_var($row->getSourceProperty('Product Name'), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

    $metatags = serialize([
      'title' => preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $clearstring),
      'description' => $description,
      'keywords' => '',
    ]);

    $row->setSourceProperty('metatags', $metatags);


    /* @var Row $row */
    if (!parent::prepareRow($row)) {
      return FALSE;
    }



    // Categories
    $tids = array();

    $categories_groups = explode('||', $row->getSourceProperty('WebCategory'));

    foreach ($categories_groups as $categories_group){

      $categories = explode(',', $categories_group);

      $vid = $row->getSourceProperty('Vocabulary');

      // If there is only one category then no further processing is needed

      if (count($categories) == 1) {

        // Lookup TID from name
        $tid = getTidByName($categories[0], $vid);
        $tids[] = $tid[0];

      } elseif (count($categories) == 2) {

        $connection = \Drupal::database();

        // One Parent
        $vid = $row->getSourceProperty('Vocabulary');
        $terms = getTidByName($categories[1], $vid);
        $parents = getTidByName($categories[0], $vid);

        foreach ($parents as $parent) {
          break;
        }
        foreach ($terms as $term) {
          // Look for a match
          $tid = $connection->query("SELECT entity_id FROM {taxonomy_term__parent} WHERE entity_id = :tid AND parent_target_id = :pid", [':tid' => $term, ':pid' => $parent])->fetchField();
          if ($tid) {
            // Found match!
            $tids[] = $tid;
          }
        }

      } elseif (count($categories) == 3) {

        $connection = \Drupal::database();

        // Two Parents
        $vid = $row->getSourceProperty('Vocabulary');
        $terms = getTidByName($categories[2], $vid);
        $parents = getTidByName($categories[1], $vid);

        foreach ($parents as $parent) {
          break;
        }
        foreach ($terms as $term) {
          // Look for a match
          $tid = $connection->query("SELECT entity_id FROM {taxonomy_term__parent} WHERE entity_id = :tid AND parent_target_id = :pid", [':tid' => $term, ':pid' => $parent])->fetchField();
          if ($tid) {
            // Found match!
            $tids[] = $tid;
          }
        }
      } elseif (count($categories) == 4) {

        $connection = \Drupal::database();

        // Two Parents
        $vid = $row->getSourceProperty('Vocabulary');
        $terms = getTidByName($categories[3], $vid);
        $parents = getTidByName($categories[2], $vid);

        foreach ($parents as $parent) {
          break;
        }
        foreach ($terms as $term) {
          // Look for a match
          $tid = $connection->query("SELECT entity_id FROM {taxonomy_term__parent} WHERE entity_id = :tid AND parent_target_id = :pid", [':tid' => $term, ':pid' => $parent])->fetchField();
          if ($tid) {
            // Found match!
            $tids[] = $tid;
          }
        }
      }
    }


    // WebCategories2

    /*
    $categories = explode(',', $row->getSourceProperty('WebCategory2'));
    $vid = $row->getSourceProperty('Vocabulary');

    // If there is only one category then no further processing is needed

    if (count($categories) == 1 && !empty($categories[0])) {

      // Lookup TID from name
      $tid = getTidByName($categories[0], $vid);
      $tids[] = $tid[0];

    } elseif (count($categories) == 2) {

      $connection = \Drupal::database();

      // One Parent
      $vid = $row->getSourceProperty('Vocabulary');
      $terms = getTidByName($categories[1], $vid);
      $parents = getTidByName($categories[0], $vid);

      foreach ($parents as $parent) {
        break;
      }
      foreach ($terms as $term) {
        // Look for a match
        $tid = $connection->query("SELECT entity_id FROM {taxonomy_term__parent} WHERE entity_id = :tid AND parent_target_id = :pid", [':tid' => $term, ':pid' => $parent])->fetchField();
        if ($tid) {
          // Found match!
          $tids[] = $tid;
        }
      }

    } elseif (count($categories) == 3) {

      $connection = \Drupal::database();

      // Two Parents
      $vid = $row->getSourceProperty('Vocabulary');
      $terms = getTidByName($categories[2], $vid);
      $parents = getTidByName($categories[1], $vid);

      foreach ($parents as $parent) {
        break;
      }
      foreach ($terms as $term) {
        // Look for a match
        $tid = $connection->query("SELECT entity_id FROM {taxonomy_term__parent} WHERE entity_id = :tid AND parent_target_id = :pid", [':tid' => $term, ':pid' => $parent])->fetchField();
        if ($tid) {
          // Found match!
          $tids[] = $tid;
        }
      }
    } elseif (count($categories) == 4) {

      $connection = \Drupal::database();

      // Two Parents
      $vid = $row->getSourceProperty('Vocabulary');
      $terms = getTidByName($categories[3], $vid);
      $parents = getTidByName($categories[2], $vid);

      foreach ($parents as $parent) {
        break;
      }
      foreach ($terms as $term) {
        // Look for a match
        $tid = $connection->query("SELECT entity_id FROM {taxonomy_term__parent} WHERE entity_id = :tid AND parent_target_id = :pid", [':tid' => $term, ':pid' => $parent])->fetchField();
        if ($tid) {
          // Found match!
          $tids[] = $tid;
        }
      }
    }
    */

    $row->setSourceProperty('tid', $tids);


/*
    $address = $row->getSourceProperty('address_line1');
    if (!empty($address)){
      $row->setSourceProperty('Country', 'US');
    }
*/


    $query = \Drupal::entityQuery('commerce_product_variation')->condition('field_base_sku', $sku_prefix, 'STARTS_WITH');

    $values = $query->execute();

    $targets = [];
    foreach ($values as $value) {
      $variation_id = $value;

      $title = db_query('SELECT title FROM {commerce_product_variation_field_data} WHERE 	variation_id  = :variation_id',
        array(':variation_id' => $variation_id))->fetchField();

      $targets[] = ['target_id' => $value, 'title' => $title];
    }

    usort($targets, function ($item1, $item2) {
      return $item1['title'] <=> $item2['title'];
    });

    $row->setDestinationProperty('variations', $targets);
    $row->rehash();

    $allow_image_upload = $row->getSourceProperty('Logo');
    if ($allow_image_upload == 'Y'){
      $row->setSourceProperty('allow_image_upload', 1);
    }else{
      $row->setSourceProperty('allow_image_upload', 0);
    }

    /*
    $clearstring = filter_var($row->getSourceProperty('Product Name'), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $row->setSourceProperty('Product Name', $clearstring);

    $clearstring = filter_var($row->getSourceProperty('Product Description'), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $row->setSourceProperty('Product Description', $clearstring);
*/

    return TRUE;
  }
}

