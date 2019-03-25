<?php

/**
 * @file
 * Contains \Drupal\awards_importers\Plugin\migrate\source\awardsProductsVariation.
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
 *   id = "awards_products_variation"
 * )
 */
class AwardsProductsVariation extends CSV{

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row)  {

    // Qty Discounts

    //$discounts = explode('||', $row->getSourceProperty('Volume Quantity And Price'));
    $discounts = explode(',', $row->getSourceProperty('Volume Quantity And Price'));

    if (isset($discounts[0]) && isset($discounts[0])){
      unset($discounts[0]);
      $discount_array = array();
      foreach($discounts as $discount){
        $discounts = explode('||', $discount);
        if (isset($discounts[0]) && isset($discounts[1])){
          $discount_array[] = array(
              'threshold' => $discounts[0],
              'price' => $discounts[1],
          );
        }
      }
      $row->setSourceProperty('Discounts', $discount_array);
    }


    // Mix and Match Price Breaks
    $discounts = explode('||', $row->getSourceProperty('MixMatch'));

    if (isset($discounts[0]) && isset($discounts[1])){

      $sku_match = $discounts[0];
      
      unset($discounts[0]);
      unset($discounts[1]);
      $discount_array = array();
      foreach($discounts as $discount){
        $discounts = explode(',', $discount);
        if (isset($discounts[0]) && isset($discounts[1])){
          $discount_array[] = array(
            'sku' => $sku_match,
            'threshold' => $discounts[1],
            'price' => $discounts[0],
          );
        }
      }
      $row->setSourceProperty('MixMatch', $discount_array);
    }

    /*
    $clearstring = filter_var($row->getSourceProperty('Product Name'), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $row->setSourceProperty('Product Name', $clearstring);

    $clearstring = filter_var($row->getSourceProperty('Product Description'), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $row->setSourceProperty('Product Description', $clearstring);
*/

    $productSpecificAttribute = explode('||', $row->getSourceProperty('ProductSpecificAttribute')); // All ProductSpecificAttribute split into an array
    if (isset($productSpecificAttribute[0]) && isset($productSpecificAttribute[0])){
     // \Drupal::logger('$ProductSpecificAttribute')->notice('<pre>' . print_r($productSpecificAttribute,1) . '</pre>');

      $title = $row->getSourceProperty('Product Name');

      if (isset($productSpecificAttribute[0]) && isset($productSpecificAttribute[1])){

        if ($productSpecificAttribute[0] == 'Size'){
          $row->setSourceProperty('var_size', $productSpecificAttribute[1]);
        }

        if ($productSpecificAttribute[0] == 'Size'){
          $attributes =  $productSpecificAttribute[1] . ' ' . $title;
        }else{
          $attributes =  $productSpecificAttribute[0] . ': ' . $productSpecificAttribute[1] . ' ' . $title;
        }


        $row->setSourceProperty('Product Name', $attributes);
      }else{
        $row->setSourceProperty('Product Name', $title);
      }

    }
  }
}