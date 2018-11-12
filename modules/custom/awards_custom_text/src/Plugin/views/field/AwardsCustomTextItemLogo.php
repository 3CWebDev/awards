<?php

namespace Drupal\awards_custom_text\Plugin\views\field;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\UncacheableFieldHandlerTrait;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Class AwardsCustomTextItemLogo
 *
 * @ViewsField("awards_custom_text_item_logo")
 */
class AwardsCustomTextItemLogo extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    if (!isset($values->_entity->order_item_id)){
      if (isset($values->_relationship_entities['order_items'])){
        $order_item_id = $values->_relationship_entities['order_items']->order_item_id->getString();
      }
    }else{
      $order_item_id = $values->_entity->order_item_id->getString();
    }

    $order_item = \Drupal\commerce_order\Entity\OrderItem::load($order_item_id);
    if ($order_item->field_order_item_logo->getString()){
      switch ($order_item->field_order_item_logo->getString()){
        case 'no_logo':
          $output = '<p>No logo</p>';
          return check_markup($output, 'full_html');
          break;
        case 'stock_logo':
          $output = '<p>Stock logo</p>';
          $target_id = $order_item->field_order_item_stock_image->getString();
          $term = \Drupal\taxonomy\Entity\Term::load($target_id);
          $url = file_create_url($term->field_stock_image->entity->getFileUri());

          // Check for permission
          $user = \Drupal::currentUser();
          if ($user->hasPermission('administer commerce_order')){
            $output .= '<p>Name: <a href="/taxonomy/term/' . $term->tid->getString() . '">' . $term->name->getString() . '</a></p>';
            $output .= '<p>Download: <a target="_blank" href="' . $url .'">download</a></p>';
          }else{

            $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('thumbnail');
            $image_url = $style->buildUrl($term->field_stock_image->entity->getFileUri());

            $output .= '<p>' . $term->name->getString() . '</p>';
            $output .= '<img src="' . $image_url . '" />';
          }
          return check_markup($output, 'full_html');
          break;
        case 'custom_logo':
          $output = '<p>Custom logo</p>';

          $url = file_create_url($order_item->field_custom_prod_image->entity->getFileUri());

          $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('thumbnail');
          $image_url = $style->buildUrl($order_item->field_custom_prod_image->entity->getFileUri());

          $output .= '<p><a target="_blank" target="_blank" href="' . $url .'"><img src="' . $image_url . '" /></a></p>';
          $user = \Drupal::currentUser();
          if ($user->hasPermission('administer commerce_order')) {
            $output .= '<p>Download: <a target="_blank" href="' . $url . '">download</a></p>';
          }
          return check_markup($output, 'full_html');
          break;
        case 'logo_on_file':
          $output = '<p>Logo on File</p>';
          //$output .= '<p>' . $order_item->field_logo_is_on_file->getString() . '</p>';
          return check_markup($output, 'full_html');
          break;
      }
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