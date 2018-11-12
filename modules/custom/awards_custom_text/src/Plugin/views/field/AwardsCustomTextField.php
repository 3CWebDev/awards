<?php
namespace Drupal\awards_custom_text\Plugin\views\field;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\UncacheableFieldHandlerTrait;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Class AwardsCustomTextField
 *
 * @ViewsField("awards_custom_text_field")
 */
class AwardsCustomTextField extends FieldPluginBase {

  use UncacheableFieldHandlerTrait;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * {@inheritdoc}
   */
  /**
   * Constructs a new EditRemove object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartManagerInterface $cart_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->cartManager = $cart_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('commerce_cart.cart_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function clickSortable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $row, $field = NULL) {

    return '<!--form-item-' . $this->options['id'] . '--' . $row->index . '-->';
  }

  /**
   * Form constructor for the views form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function viewsForm(array &$form, FormStateInterface $form_state) {
    // Make sure we do not accidentally cache this form.
    $form['#cache']['max-age'] = 0;

    // The view is empty, abort.
    if (empty($this->view->result)) {
      unset($form['actions']);
      return;
    }


    $form[$this->options['id']]['#tree'] = TRUE;
    foreach ($this->view->result as $row_index => $row) {

      $product_variation = $row->_relationship_entities['order_items']->getPurchasedEntity();

      if ($product_variation){
        $product_id = $product_variation->product_id->getString();
        $product = \Drupal\commerce_product\Entity\Product::load($product_id);

        $repeat_order = $row->_relationship_entities['order_items']->field_repeat_order->getString();
        if ($repeat_order == 2){
          $form[$this->options['id']][$row_index] = [
            '#markup' => 'Repeat Order Selected',

          ];
          return;
        }
        if ($product->hasField('field_number_of_lines') && is_numeric($product->field_number_of_lines->getString()) && $product->field_number_of_lines->getString() > 0){

          $custom_text_entered = $row->_relationship_entities['order_items']->field_custom_text_entered->getString();

          if ($custom_text_entered){
            $form[$this->options['id']][$row_index] = [
              '#type' => 'submit',
              '#value' => t('Review/Edit Custom Text'),
              '#name' => 'edit-order-item-' . $row->_relationship_entities['order_items']->order_item_id->getString(),
              '#edit_custom_text' => TRUE,
              '#row_index' => $row->_relationship_entities['order_items']->order_item_id->getString(),
              '#attributes' => ['class' => ['customize-text-button']],
            ];
          }else{
            $form[$this->options['id']][$row_index] = [
              '#type' => 'submit',
              '#value' => t('Custom Text Needed'),
              '#name' => 'edit-order-item-' . $row->_relationship_entities['order_items']->order_item_id->getString(),
              '#edit_custom_text' => TRUE,
              '#row_index' => $row->_relationship_entities['order_items']->order_item_id->getString(),
              '#attributes' => ['class' => ['btn-warning']],
            ];
          }
        }else{
          $form[$this->options['id']][$row_index] = [
            '#markup' => 'n/a',

          ];
        }
      }


    }
  }

  /**
   * Submit handler for the views form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function viewsFormSubmit(array &$form, FormStateInterface $form_state) {

    $triggering_element = $form_state->getTriggeringElement();

    if (!empty($triggering_element['#edit_custom_text'])) {
      $row_index = $triggering_element['#row_index'];
      $order_item = \Drupal\commerce_order\Entity\OrderItem::load($row_index);
      $variation = \Drupal\commerce_product\Entity\ProductVariation::load($order_item->purchased_entity->getString());

      $path = '/product/' . $variation->product_id->getString() . '/' . $row_index . '/custom_text';
      $response = new RedirectResponse($path, 302);
      $response->send();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing.
  }
}