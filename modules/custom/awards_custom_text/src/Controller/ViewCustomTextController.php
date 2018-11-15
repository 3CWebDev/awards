<?php
namespace Drupal\awards_custom_text\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Provides route responses for the Example module.
 */
class ViewCustomTextController extends ControllerBase {

  /**
   * Returns a page displaying the custom text that was entered.
   *
   * @return array
   *   A simple renderable array.
   */
  public function ViewCustomText($order_item_id) {

    $order_item = \Drupal\commerce_order\Entity\OrderItem::load($order_item_id);

    if ($order_item->field_custom_text_entered->getString() == 1){
      $custom_texts = $order_item->field_item_custom_text->getValue();
      foreach ($custom_texts as $key => $custom_text){

        $value .= '<h3>Item #' . ($key+1) . '</strong></h3>';

        $target_id = $custom_text['target_id'];
        $paragraph = \Drupal\paragraphs\Entity\Paragraph::load($target_id);
        $text_items = $paragraph->field_prod_engraving_text->getValue();
        $value .= '<div>';
        foreach ($text_items as $text_item){
          if ($text_item['value'] != '<br />'){
            $value .= $text_item['value'] . '<br />';
          }
        }
      
      }

      $value = check_markup($value, 'full_html');
    }

    $element = array(
        '#markup' => $value,
    );

    return $element;
  }

}