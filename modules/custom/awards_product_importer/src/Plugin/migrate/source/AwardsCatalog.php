<?php

/**
 * @file
 * Contains \Drupal\awards_importers\Plugin\migrate\source\awardsCatalog.
 */

namespace Drupal\awards_product_importer\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;


/**
 * Source plugin for catalog term entities
 *
 * @MigrateSource(
 *   id = "awards_catalog"
 * )
 */
class AwardsCatalog extends CSV {

  /**
   * {@inheritdoc}
   */
  public function getIDs() {
    $ids = [];
    foreach ($this->configuration['keys'] as $delta => $value) {
      if (is_array($value)) {
        $ids[$delta] = $value;
      }
      else {
        $ids[$value]['type'] = 'string';
      }
    }
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    $categories = explode('|', $row->getSourceProperty('Terms')); // All categories split into an array

    $last_category = array_values(array_slice($categories, -1))[0]; // The last/top-level category
    //$last_category = explode('^', $last_category);
    //\Drupal::logger('$last_category')->notice('<pre>' . print_r($last_category, 1) . '</pre>');
    $parent = FALSE;
//\Drupal::logger('$last_category')->notice($last_category);
    foreach ($categories as $category){
//\Drupal::logger('$category')->notice($category);

      //$category = explode('^', $category);

      // Does the term exist already?
      //$terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(array('field_catalog_id' => $category[0]));
      //$terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(array('name' => $category[1]));
      //$term = awards_importers_getTidByName($category[1], 'catalog_category');
      $term = awards_importers_getTidByName($category, 'catalog_category');
      //\Drupal::logger($category[1])->notice('<pre>' . print_r($terms, 1) . '</pre>');
      //$term = reset($terms);

      // If the Term doesn't exist then create it
      // We only want to do this for children - the top term will be created by Migrate
      //if ($last_category[1] != $category[1]){
        if ($last_category != $category){
          if (!$term) {

            $term = Term::create([
                //'name' => $category[1],
                'name' => $category,
                'vid' => 'catalog_category',
                'field_category_image' => array(),
                //'field_catalog_id' => $category[0],
                'field_catalog_id' => $category,
            ]);
            if ($parent){

              $term->parent = array($parent->id());
            }
            $term->save();
            $parent = $term;
          }else{
            if ($parent){
              $term->parent = array($parent->id());
              $term->save();
            }
            $parent = $term;
        }
      }
    }

    //$row->setSourceProperty('name', $last_category[1]);
    //$row->setSourceProperty('id', $last_category[0]);
    $row->setSourceProperty('name', $last_category);
    $row->setSourceProperty('id', $last_category);

    if ($parent) {
      $row->setSourceProperty('parent', $parent->id());
    }

    // Set images for top level term
    $images = array();
    if ($image = $row->getSourceProperty('Image')){
      //$images[]  = array('uri' => $image, 'title' => $last_category[1], 'alt' => $last_category[1]);
      $images[]  = array('uri' => $image, 'title' => $last_category, 'alt' => $last_category);
    }

    $row->setSourceProperty('Images', $images);

  }
}


/**
 * Utility: find term by name and vid.
 * @param null $name
 *  Term name
 * @param null $vid
 *  Term vid
 * @return int
 *  Term or 0 if none.
 */
function awards_importers_getTidByName($name = NULL, $vid = NULL) {
  $properties = [];
  if (!empty($name)) {
    $properties['name'] = $name;
  }
  if (!empty($vid)) {
    $properties['vid'] = $vid;
  }
  $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
  $term = reset($terms);
  if (empty($term)){
  }
  return !empty($term) ? $term : 0;
}