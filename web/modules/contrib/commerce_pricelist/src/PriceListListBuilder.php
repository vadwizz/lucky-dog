<?php

namespace Drupal\commerce_pricelist;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the list builder for price lists.
 */
class PriceListListBuilder extends EntityListBuilder implements FormInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The entities being listed.
   *
   * @var \Drupal\commerce_promotion\Entity\PromotionInterface[]
   */
  protected $entities = [];

  /**
   * Whether tabledrag is enabled.
   *
   * @var bool
   */
  protected $hasTableDrag = TRUE;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $instance = parent::createInstance($container, $entity_type);
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_pricelists';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort('weight', 'ASC')
      ->sort('id', 'ASC');
    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['start_date'] = $this->t('Start date');
    $header['end_date'] = $this->t('End date');
    if ($this->hasTableDrag) {
      $header['weight'] = $this->t('Weight');
    }
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_pricelist\Entity\PriceListInterface $entity */
    $row['#attributes']['class'][] = 'draggable';
    $row['#weight'] = $entity->getWeight();
    $row['name'] = $entity->label();
    if (!$entity->isEnabled()) {
      $row['name'] .= ' (' . $this->t('Disabled') . ')';
    }
    $row['start_date'] = $entity->getStartDate()->format('M jS Y H:i:s');
    $row['end_date'] = $entity->getEndDate() ? $entity->getEndDate()->format('M jS Y H:i:s') : '—';
    if ($this->hasTableDrag) {
      $row['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $entity->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $entity->getWeight(),
        '#attributes' => ['class' => ['weight']],
      ];
    }
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = $this->formBuilder->getForm($this);
    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $build['pager'] = [
        '#type' => 'pager',
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->entities = $this->load();
    if (count($this->entities) <= 1) {
      $this->hasTableDrag = FALSE;
    }
    $delta = 10;
    // Dynamically expand the allowed delta based on the number of entities.
    $count = count($this->entities);
    if ($count > 20) {
      $delta = ceil($count / 2);
    }

    $form['price_lists'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#empty' => $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]),
    ];
    foreach ($this->entities as $entity) {
      $row = $this->buildRow($entity);
      $row['name'] = ['#markup' => $row['name']];
      $row['start_date'] = ['#markup' => $row['start_date']];
      $row['end_date'] = ['#markup' => $row['end_date']];
      if (isset($row['weight'])) {
        $row['weight']['#delta'] = $delta;
      }
      $form['price_lists'][$entity->id()] = $row;
    }

    if ($this->hasTableDrag) {
      $form['price_lists']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'weight',
      ];
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => t('Save'),
        '#button_type' => 'primary',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // No validation.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('price_lists') as $id => $value) {
      if (isset($this->entities[$id]) && $this->entities[$id]->getWeight() != $value['weight']) {
        // Save entity only when its weight was changed.
        $this->entities[$id]->setWeight($value['weight']);
        $this->entities[$id]->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->access('update')) {
      $operations['prices'] = [
        'title' => $this->t('Prices'),
        'url' => new Url('entity.commerce_pricelist_item.collection', [
          'commerce_pricelist' => $entity->id(),
        ]),
      ];
    }

    return $operations;
  }

}
