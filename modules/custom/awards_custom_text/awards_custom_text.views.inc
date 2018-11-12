<?php

/**
* @file
* Provide views data for the Order module.
*/

/**
* Implements hook_views_data().
*/
function awards_custom_text_views_data() {

  $data['commerce_order_item']['custom_text'] = [
      'title' => t('Custom Text Display'),
      'help' => t('Displays the custom text of an order item.'),
      'field' => [
        'id' => 'awards_custom_text_field',
      ],
  ];

  $data['commerce_order_item']['custom_text_admin'] = [
      'title' => t('Custom Text Admin Display'),
      'help' => t('Displays the custom text of a order item in the order admin'),
      'field' => [
          'id' => 'awards_custom_text_field_admin',
      ],
  ];
  $data['commerce_order_item']['custom_item_logo'] = [
      'title' => t('Item Logo'),
      'help' => t('Displays the order item\'s logo image'),
      'field' => [
          'id' => 'awards_custom_text_item_logo',
      ],
  ];

  return $data;
}