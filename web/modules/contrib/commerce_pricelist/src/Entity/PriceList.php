<?php

namespace Drupal\commerce_pricelist\Entity;

use Drupal\commerce\EntityHelper;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

/**
 * Defines the Price list entity.
 *
 * @ContentEntityType(
 *   id = "commerce_pricelist",
 *   label = @Translation("Price list"),
 *   label_collection = @Translation("Price lists"),
 *   label_singular = @Translation("price list"),
 *   label_plural = @Translation("price lists"),
 *   label_count = @PluralTranslation(
 *     singular = "@count price list",
 *     plural = "@count price lists",
 *   ),
 *   handlers = {
 *     "event" = "Drupal\commerce_pricelist\Event\PriceListEvent",
 *     "storage" = "Drupal\commerce\CommerceContentEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_pricelist\PriceListListBuilder",
 *     "views_data" = "Drupal\commerce\CommerceEntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_pricelist\Form\PriceListForm",
 *       "add" = "Drupal\commerce_pricelist\Form\PriceListForm",
 *       "edit" = "Drupal\commerce_pricelist\Form\PriceListForm",
 *       "duplicate" = "Drupal\commerce_pricelist\Form\PriceListForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\commerce_pricelist\PriceListRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer commerce_pricelist",
 *   base_table = "commerce_pricelist",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "owner" = "uid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "add-page" = "/price-list/add",
 *     "add-form" = "/price-list/add/{type}",
 *     "edit-form" = "/price-list/{commerce_pricelist}/edit",
 *     "duplicate-form" = "/price-list/{commerce_pricelist}/duplicate",
 *     "delete-form" = "/price-list/{commerce_pricelist}/delete",
 *     "delete-multiple-form" = "/admin/commerce/price-lists/delete",
 *     "collection" = "/admin/commerce/price-lists",
 *   },
 *   field_ui_base_route = "entity.commerce_pricelist.bundle_page"
 * )
 */
class PriceList extends CommerceContentEntityBase implements PriceListInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStores() {
    return $this->getTranslatedReferencedEntities('stores');
  }

  /**
   * {@inheritdoc}
   */
  public function setStores(array $stores) {
    $this->set('stores', $stores);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStoreIds() {
    $store_ids = [];
    foreach ($this->get('stores') as $field_item) {
      $store_ids[] = $field_item->target_id;
    }
    return $store_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function setStoreIds(array $store_ids) {
    $this->set('stores', $store_ids);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomer() {
    if ($this->get('customers')->isEmpty()) {
      return NULL;
    }
    return $this->get('customers')->get(0)->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomers() {
    $customers = [];
    foreach ($this->get('customers') as $field_item) {
      if ($field_item->isEmpty() || !$field_item->entity) {
        continue;
      }
      $customers[] = $field_item->entity;
    }
    return $customers;
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomer(UserInterface $user) {
    return $this->setCustomers([$user]);
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomers(array $users) {
    $this->set('customers', $users);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomerId() {
    if ($this->get('customers')->isEmpty()) {
      return NULL;
    }
    return $this->get('customers')->get(0)->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomerId($uid) {
    $this->set('customers', ['target_id' => $uid]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomerRoles() {
    $roles = [];
    foreach ($this->get('customer_roles') as $field_item) {
      $roles[] = $field_item->target_id;
    }
    return $roles;
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomerRoles(array $rids) {
    $this->set('customer_roles', $rids);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate($store_timezone = 'UTC') {
    return new DrupalDateTime($this->get('start_date')->value, $store_timezone);
  }

  /**
   * {@inheritdoc}
   */
  public function setStartDate(DrupalDateTime $start_date) {
    $this->get('start_date')->value = $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate($store_timezone = 'UTC') {
    if (!$this->get('end_date')->isEmpty()) {
      return new DrupalDateTime($this->get('end_date')->value, $store_timezone);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setEndDate(DrupalDateTime $end_date = NULL) {
    $this->get('end_date')->value = NULL;
    if ($end_date) {
      $this->get('end_date')->value = $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return (int) $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->set('status', (bool) $enabled);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItemIds() {
    if ($this->isNew()) {
      return [];
    }
    $query = $this->entityTypeManager()->getStorage('commerce_pricelist_item')->getQuery();
    $query
      ->accessCheck(FALSE)
      ->condition('price_list_id', $this->id());
    $result = $query->execute();

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    $price_list_item_storage = \Drupal::entityTypeManager()->getStorage('commerce_pricelist_item');
    $query = $price_list_item_storage->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('price_list_id', EntityHelper::extractIds($entities), 'IN');
    $result = $query->execute();
    if (!empty($result)) {
      // @todo This can crash due to there potentially being thousands of items.
      $price_list_items = $price_list_item_storage->loadMultiple($result);
      $price_list_item_storage->delete($price_list_items);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['uid']
      ->setLabel(t('Owner'))
      ->setDescription(t('The user that owns this price list.'));

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the price list.'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['stores'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Stores'))
      ->setDescription(t('The stores for which the price list is valid.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'commerce_store')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_entity_select',
        'weight' => 2,
      ]);

    $fields['customers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Customers'))
      ->setDescription(t('The customers for which the price list is valid.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ]);

    $fields['customer_roles'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Customer roles'))
      ->setDescription(t('The customer roles for which the price list is valid.'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'user_role')
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
      ]);

    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start date'))
      ->setDescription(t('The date the price list becomes valid.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'datetime')
      ->setDefaultValueCallback('Drupal\commerce_pricelist\Entity\PriceList::getDefaultStartDate')
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 5,
      ]);

    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End date'))
      ->setDescription(t('The date after which the price list is invalid.'))
      ->setRequired(FALSE)
      ->setSetting('datetime_type', 'datetime')
      ->setSetting('datetime_optional_label', t('Provide an end date'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_store_datetime',
        'weight' => 6,
      ]);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of this price list in relation to other price lists.'))
      ->setDefaultValue(0);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether the price list is enabled.'))
      ->setDefaultValue(TRUE)
      ->setRequired(TRUE)
      ->setSettings([
        'on_label' => t('Enabled'),
        'off_label' => t('Disabled'),
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
      ]);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the price list was last edited.'));

    return $fields;
  }

  /**
   * Default value callback for 'start_date' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return string
   *   The default value (date string).
   */
  public static function getDefaultStartDate() {
    $timestamp = \Drupal::time()->getRequestTime();
    return date(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $timestamp);
  }

  /**
   * Helper callback for uasort() to sort price lists by weight and label.
   *
   * @param \Drupal\commerce_pricelist\Entity\PriceListInterface $a
   *   The first price list to sort.
   * @param \Drupal\commerce_pricelist\Entity\PriceListInterface $b
   *   The second priice list to sort.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sort(PriceListInterface $a, PriceListInterface $b) {
    $a_weight = $a->getWeight();
    $b_weight = $b->getWeight();
    if ($a_weight == $b_weight) {
      $a_label = $a->label();
      $b_label = $b->label();
      return strnatcasecmp($a_label, $b_label);
    }
    return ($a_weight < $b_weight) ? -1 : 1;
  }

}
