<?php
namespace Drupal\awards_product_importer\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class TestController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function test() {

    $vid = 'product_categories';


    $connection = \Drupal::database();

    // One parent
    $terms = getTidByName('Art Glass', $vid);
    $parents = getTidByName('Glass', $vid);

    dpm('Terms');
    dpm($terms);

    dpm('Parents');
    dpm($parents);

    foreach ($parents as $parent){
      break;
    }


    foreach ($terms as $term){
      // Look for a match
      //\Drupal::logger('Double tid')->notice($term->id());
      //\Drupal::logger('Double pid')->notice($parent->id());
      $tid = $connection->query("SELECT entity_id FROM {taxonomy_term__parent} WHERE entity_id = :tid AND parent_target_id = :pid", [':tid' => $term, ':pid' => $parent])->fetchField();

      if ($tid){
        // Found match!
        dpm('Match!');
        //$row->setSourceProperty('tid', $tid);
      }
    }

    $element = array(
      '#markup' => 'Hello, world',
    );
    return $element;
  }

}