<?php

namespace Drupal\commerce_customizations\Plugin\Commerce\Condition;

use Drupal\commerce\EntityUuidMapperInterface;
use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the product category condition for orders.
 *
 * @CommerceCondition(
 *   id = "order_product_category_custom",
 *   label = @Translation("Product category"),
 *   display_label = @Translation("Order contains product categories Custom"),
 *   category = @Translation("Products"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderProductCategoryCustom extends ConditionBase implements ContainerFactoryPluginInterface {

  use ProductCategoryTrait;

  /**
   * Constructs a new OrderProductCategory object.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce\EntityUuidMapperInterface $entity_uuid_mapper
   *   The entity UUID mapper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager, EntityUuidMapperInterface $entity_uuid_mapper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityUuidMapper = $entity_uuid_mapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager'),
      $container->get('commerce.entity_uuid_mapper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity) {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;
    $term_ids = $this->getTermIds();

    $does_not_match_term = FALSE;
    $match_term = FALSE;

    foreach ($order->getItems() as $order_item) {
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $purchased_entity */
      $purchased_entity = $order_item->getPurchasedEntity();
      if (!$purchased_entity || $purchased_entity->getEntityTypeId() != 'commerce_product_variation') {
        continue;
      }
      $referenced_ids = $this->getReferencedIds($purchased_entity->getProduct());
      if (array_intersect($term_ids, $referenced_ids)) {
        $match_term = TRUE;
      }else{
        $does_not_match_term = TRUE;
      }
    }

    if ($match_term && !$does_not_match_term){
      return TRUE;
    }

    return FALSE;
  }

}
