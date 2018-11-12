<?php

namespace Drupal\awards_custom_text\Plugin\views\field;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\UncacheableFieldHandlerTrait;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
#use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class AwardsCustomTextFieldAdmin
 *
 * @ViewsField("awards_custom_text_field_admin")
 */
class AwardsCustomTextFieldAdmin extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $order_item_id = $values->_entity->order_item_id->getString();

    $order_item = \Drupal\commerce_order\Entity\OrderItem::load($order_item_id);
    $repeat_order = $order_item->field_repeat_order->getString();

    if ($repeat_order == 2){
      $value = '<p><u>Repeat Order Selected</u></p>';
      $value .= '<p>' . $order_item->field_repeat_order_description->getString() .'</p>';
      return check_markup($value, 'full_html');
    }
    if ($order_item->field_custom_text_entered->getString() == 1){
      $custom_texts = $order_item->field_item_custom_text->getValue();
      // Check for permission
      $user = \Drupal::currentUser();

      if ($user->hasPermission('administer commerce_order')) {
        $value = '<p><a href="admin/commerce/view_custom_text/' . $order_item_id . '">view all</a></p><div class="accordion">';
      }else{
        $value = '</p><div class="accordion">';
      }

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
        $value .= '</div>';
      }
      $value .= '</div>';
      return check_markup($value, 'full_html');
    }
    return 'n/a';
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing.
  }
}