<?php

namespace Drupal\awards_custom\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CustomCartEventSubscriber implements EventSubscriberInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;


  /**
   * CustomCartEventSubscriber constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
        CartEvents::CART_ENTITY_ADD => 'checkIfCustomizable',
        KernelEvents::RESPONSE => ['checkRedirectIssued', -10],
    ];
    return $events;
  }

  /**
   * Tries to jump to checkout, skipping cart after adding certain items.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The add to cart event.
   */
  public function checkIfCustomizable(CartEntityAddEvent $event) {

    $purchased_entity = $event->getEntity();
    $product_id = $purchased_entity->product_id->getString();
    $product = \Drupal\commerce_product\Entity\Product::load($product_id);

    /* Check if cart item contains custom text option and, if so, has the text been entered?*/

    if (isset($product->field_number_of_lines)){

      $number_of_lines = $product->field_number_of_lines->getString();
      if (is_numeric($number_of_lines)){
        $order_item = $event->getOrderItem();
        $custom_text_entered = $order_item->field_custom_text_entered->getString();
        if ($custom_text_entered != 1){

          drupal_set_message(t('@entity requires engraving customzation.', [
              '@entity' => $product->title->getString() . ' ' . $purchased_entity->label(),
          ]), 'warning');

          $url = '/product/' . $product_id . '/' . $order_item->id() . '/custom_text';
          $this->requestStack->getCurrentRequest()->attributes->set('awards_custom_jump_to_checkout_url', $url);

        }
      }
    }
  }

  /**
   * Checks if a redirect url has been set.
   *
   * Redirects to the provided url if there is one.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event.
   */
  public function checkRedirectIssued(FilterResponseEvent $event) {
    $request = $event->getRequest();
    $redirect_url = $request->attributes->get('awards_custom_jump_to_checkout_url');
    if (isset($redirect_url)) {
      $event->setResponse(new RedirectResponse($redirect_url));
    }
  }

}