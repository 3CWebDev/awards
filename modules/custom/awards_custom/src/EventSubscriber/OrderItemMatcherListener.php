<?php

namespace Drupal\awards_custom\EventSubscriber;

use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\OrderItemComparisonFieldsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderItemMatcherListener implements EventSubscriberInterface {

  /**
  * Make sure we keep OrderItems separate looking at certain fields.
  *
  * @param OrderItemComparisonFieldsEvent $event
  */
  public function onSave(OrderItemComparisonFieldsEvent $event) {
    
    //\Drupal::logger('test')->notice('true');
    $fields = $event->getComparisonFields();
    $fields[] = 'field_custom_text_plaque';

    $event->setComparisonFields($fields);
  }

  /**
  * {@inheritdoc}
  */
  public static function getSubscribedEvents() {
    $events[CartEvents::ORDER_ITEM_COMPARISON_FIELDS][] = ['onSave'];

    return $events;
  }
}