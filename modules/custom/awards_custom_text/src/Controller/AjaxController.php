<?php

namespace Drupal\awards_custom_text\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\paragraphs\Entity\Paragraph;

class AjaxController extends ControllerBase{

  // $variable is the wildcard from the route
  public function ajaxCallback($variable){

    $paragraph = \Drupal\paragraphs\Entity\Paragraph::load($variable);
    $text = $paragraph->field_template_engraving_text->getValue();
    $data = array();

    foreach ($text as $text_line){
      $data[] = $text_line['value'];
    }

    asort($data);
    
    $response = new AjaxResponse();
    $response->setData($data);
    
    return $response;
  }
}