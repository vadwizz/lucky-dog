<?php

namespace Drupal\commerce_pricelist\Form;

use Drupal\commerce_price\Price;
use Drupal\commerce_pricelist\CsvFileObject;
use Drupal\commerce_pricelist\Entity\PriceListInterface;
use Drupal\commerce_pricelist\Entity\PriceListItemInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\PrivateStream;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Environment;

class PriceListItemImportForm extends FormBase {

  /**
   * The number of price list items to process in each batch.
   *
   * @var int
   */
  const BATCH_SIZE = 25;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PriceListItemImportForm object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_pricelist_item_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, PriceListInterface $commerce_pricelist = NULL) {
    $form_state->set('price_list_id', $commerce_pricelist->id());
    $purchasable_entity_type_id = $commerce_pricelist->bundle();
    $purchasable_entity_type = $this->entityTypeManager->getDefinition($purchasable_entity_type_id);
    $purchasable_entity_column_types = [
      $purchasable_entity_type->getKey('id'),
      $purchasable_entity_type->getKey('uuid'),
      // Product variation specific.
      'sku',
    ];
    $purchasable_entity_column_types = array_combine($purchasable_entity_column_types, $purchasable_entity_column_types);
    // Get the label for each allowed field.
    $base_field_definitions = $this->entityFieldManager->getBaseFieldDefinitions($purchasable_entity_type_id);
    foreach ($base_field_definitions as $field_name => $field_definition) {
      if (isset($purchasable_entity_column_types[$field_name])) {
        $purchasable_entity_column_types[$field_name] = $field_definition->getLabel();
      }
    }
    $default_purchasable_entity_column = $purchasable_entity_type->id();
    if (strpos($default_purchasable_entity_column, 'commerce_') === 0) {
      $default_purchasable_entity_column = str_replace('commerce_', '', $default_purchasable_entity_column);
    }
    $sample_file_path = drupal_get_path('module', 'commerce_pricelist') . '/sample_file.csv';

    $form['#tree'] = TRUE;
    $form['csv'] = [
      '#type' => 'file',
      '#title' => $this->t('Choose a file'),
      '#description' => $this->t('Unsure about the format? Download a <a href=":url">sample file</a>.', [
        ':url' => Url::fromUri('base:' . $sample_file_path)->toString(),
      ]),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
        'file_validate_size' => [Environment::getUploadMaxSize()],
      ],
      // Use the private stream when available, otherwise fallback to the
      // temporary directory.
      '#upload_location' => PrivateStream::basePath() ? 'private://' : 'temporary://',
    ];

    $form['mapping'] = [
      '#type' => 'details',
      '#title' => $this->t('CSV mapping options'),
      '#collapsible' => TRUE,
      '#open' => TRUE,
    ];
    $form['mapping']['purchasable_entity_column_type'] = [
      '#type' => 'select',
      '#title' => $this->t('@purchasable_entity_type column type', [
        '@purchasable_entity_type' => $purchasable_entity_type->getLabel(),
      ]),
      '#options' => $purchasable_entity_column_types,
      '#default_value' => isset($purchasable_entity_column_types['sku']) ? 'sku' : $purchasable_entity_type->getKey('uuid'),
      '#required' => TRUE,
    ];
    $form['mapping']['purchasable_entity_column'] = [
      '#type' => 'textfield',
      '#title' => $this->t('@purchasable_entity_type column', [
        '@purchasable_entity_type' => $purchasable_entity_type->getLabel(),
      ]),
      '#default_value' => $default_purchasable_entity_column,
      '#required' => TRUE,
    ];
    $form['mapping']['quantity_column'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Quantity column'),
      '#description' => $this->t('If left empty, quantity will default to 1.'),
      '#default_value' => 'quantity',
      '#required' => TRUE,
    ];
    $form['mapping']['list_price_column'] = [
      '#type' => 'textfield',
      '#title' => $this->t('List price column'),
      '#description' => $this->t('If left empty, no list price will be set.'),
      '#default_value' => 'list_price',
    ];
    $form['mapping']['price_column'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Price column'),
      '#default_value' => 'price',
      '#required' => TRUE,
    ];
    $form['mapping']['currency_column'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Currency column'),
      '#default_value' => 'currency_code',
      '#required' => TRUE,
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('CSV file options'),
      '#collapsible' => TRUE,
      '#open' => FALSE,
    ];
    $form['options']['delimiter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Delimiter'),
      '#size' => 5,
      '#maxlength' => 1,
      '#default_value' => ',',
      '#required' => TRUE,
    ];
    $form['options']['enclosure'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enclosure'),
      '#size' => 5,
      '#maxlength' => 1,
      '#default_value' => '"',
      '#required' => TRUE,
    ];

    $form['delete_existing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete all prices in this price list prior to import.'),
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import prices'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $all_files */
    $all_files = $this->getRequest()->files->get('files', []);
    if (empty($all_files['csv'])) {
      $form_state->setErrorByName('csv', $this->t('No CSV file was provided.'));
    }
    elseif (!$all_files['csv']->isValid()) {
      $form_state->setErrorByName('csv', $this->t('The provided CSV file is invalid.'));
    }
    else {
      $file = file_save_upload('csv', $form['csv']['#upload_validators'], $form['csv']['#upload_location'], 0, FileSystemInterface::EXISTS_RENAME);

      if (!$file) {
        $form_state->setErrorByName('csv', $this->t('An error occurred while trying to upload the CSV file, please try again later.'));
      }
      else {
        $form_state->set('csv_file', $file->id());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_storage = $this->entityTypeManager->getStorage('file');
    /** @var \Drupal\file\FileInterface $file */
    $file = $file_storage->load($form_state->get('csv_file'));
    $values = $form_state->getValues();

    $batch = [
      'title' => $this->t('Importing prices'),
      'progress_message' => '',
      'operations' => [],
      'finished' => [$this, 'finishBatch'],
    ];

    if ($values['delete_existing']) {
      $batch['operations'][] = [
        [get_class($this), 'batchDeleteExisting'],
        [$form_state->get('price_list_id')],
      ];
    }
    $batch['operations'][] = [
      [get_class($this), 'batchProcess'],
      [
        $file->getFileUri(),
        $values['mapping'],
        $values['options'],
        $form_state->get('price_list_id'),
        (bool) $values['delete_existing'],
      ],
    ];
    $batch['operations'][] = [
      [get_class($this), 'batchDeleteUploadedFile'],
      [$file->id()],
    ];

    batch_set($batch);
    $form_state->setRedirect('entity.commerce_pricelist_item.collection', [
      'commerce_pricelist' => $form_state->get('price_list_id'),
    ]);
  }

  /**
   * Batch operation to delete existing items from the price list.
   *
   * @param int $price_list_id
   *   The price list ID.
   * @param array $context
   *   The batch context.
   */
  public static function batchDeleteExisting($price_list_id, array &$context) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $price_list_storage = $entity_type_manager->getStorage('commerce_pricelist');
    $price_list_item_storage = $entity_type_manager->getStorage('commerce_pricelist_item');
    /** @var \Drupal\commerce_pricelist\Entity\PriceListInterface $price_list */
    $price_list = $price_list_storage->load($price_list_id);
    $price_list_item_ids = $price_list->getItemIds();

    if (empty($context['sandbox'])) {
      $context['sandbox']['delete_total'] = count($price_list_item_ids);
      $context['sandbox']['delete_count'] = 0;
    }

    $total_items = $context['sandbox']['delete_total'];
    $deleted = &$context['sandbox']['delete_count'];
    $remaining = $total_items - $deleted;
    $limit = (int) ($remaining < self::BATCH_SIZE) ? $remaining : self::BATCH_SIZE;

    if ($total_items == 0 || empty($price_list_item_ids)) {
      $context['finished'] = 1;
    }
    else {
      $price_list_item_ids = array_slice($price_list_item_ids, 0, $limit);
      $price_list_items = $price_list_item_storage->loadMultiple($price_list_item_ids);
      $price_list_item_storage->delete($price_list_items);
      $deleted = $deleted + $limit;

      $context['message'] = t('Deleting price @deleted of @total_items', [
        '@deleted' => $deleted,
        '@total_items' => $total_items,
      ]);
      $context['finished'] = $deleted / $total_items;
    }
    // Update the results for finishBatch().
    $context['results']['delete_count'] = $deleted;
  }

  /**
   * Batch process to import price list items from the CSV.
   *
   * @param string $file_uri
   *   The CSV file URI.
   * @param array $mapping
   *   The mapping options.
   * @param array $csv_options
   *   The CSV options.
   * @param string $price_list_id
   *   The price list ID.
   * @param bool $delete_existing
   *   The "delete existing" flag.
   * @param array $context
   *   The batch context.
   */
  public static function batchProcess($file_uri, array $mapping, array $csv_options, $price_list_id, $delete_existing, array &$context) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $price_list_storage = $entity_type_manager->getStorage('commerce_pricelist');
    $price_list_item_storage = $entity_type_manager->getStorage('commerce_pricelist_item');
    /** @var \Drupal\commerce_pricelist\Entity\PriceList $price_list */
    $price_list = $price_list_storage->load($price_list_id);
    $purchasable_entity_storage = $entity_type_manager->getStorage($price_list->bundle());
    $header_mapping = static::buildHeaderMapping($mapping);
    ini_set("auto_detect_line_endings", TRUE);
    $csv = new CsvFileObject($file_uri, TRUE, $header_mapping, $csv_options);
    if (empty($context['sandbox'])) {
      $context['sandbox']['import_total'] = (int) $csv->count();
      $context['sandbox']['import_count'] = 0;
      $context['results']['import_created'] = 0;
      $context['results']['import_updated'] = 0;
      $context['results']['import_skipped'] = 0;
    }
    // The file is invalid, stop here.
    if (!$csv->valid()) {
      $context['results']['error'] = t('The provided CSV file is invalid.');
      $context['finished'] = 1;
      return;
    }

    $import_total = $context['sandbox']['import_total'];
    $import_count = &$context['sandbox']['import_count'];
    $remaining = $import_total - $import_count;
    $limit = ($remaining < self::BATCH_SIZE) ? $remaining : self::BATCH_SIZE;

    $csv->seek($import_count + 1);
    for ($i = 0; $i < $limit; $i++) {
      $row = $csv->current();
      $row = array_map('trim', $row);
      // Skip the row if one of the required columns is empty.
      foreach (['purchasable_entity', 'price', 'currency_code'] as $required_column) {
        if (empty($row[$required_column])) {
          $context['results']['import_skipped']++;
          $import_count++;
          $csv->next();
          continue 2;
        }
      }
      $purchasable_entity = $purchasable_entity_storage->loadByProperties([
        $mapping['purchasable_entity_column_type'] => $row['purchasable_entity'],
      ]);
      $purchasable_entity = reset($purchasable_entity);
      // Skip the row if the purchasable entity could not be loaded.
      if (!$purchasable_entity) {
        $context['results']['import_skipped']++;
        $import_count++;
        $csv->next();
        continue;
      }
      $quantity = !empty($row['quantity']) ? $row['quantity'] : '1';

      // If existing price list items weren't deleted before the import,
      // try to find one to update.
      $price_list_item = NULL;
      if (!$delete_existing) {
        $result = $price_list_item_storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('type', $price_list->bundle())
          ->condition('price_list_id', $price_list->id())
          ->condition('purchasable_entity', $purchasable_entity->id())
          ->condition('quantity', $quantity)
          ->execute();

        if (!empty($result)) {
          $existing_price_list_item_id = reset($result);
          $price_list_item = $price_list_item_storage->load($existing_price_list_item_id);
          assert($price_list_item instanceof PriceListItemInterface);
        }
      }

      if (is_null($price_list_item)) {
        // No price list item was found and updated, create a new one.
        $price_list_item = $price_list_item_storage->create([
          'type' => $price_list->bundle(),
          'price_list_id' => $price_list->id(),
          'purchasable_entity' => $purchasable_entity->id(),
          'quantity' => $quantity,
        ]);
      }
      try {
        static::processRow($row, $price_list_item);
      }
      catch (\Exception $e) {
        $context['results']['import_skipped']++;
        $import_count++;
        $csv->next();
        continue;
      }

      $import_type = $price_list_item->isNew() ? 'created' : 'updated';
      $price_list_item->save();

      $import_count++;
      $context['results']['import_' . $import_type]++;
      $csv->next();
    }
    $context['message'] = t('Importing @created of @import_total price list items', [
      '@created' => $import_count,
      '@import_total' => $import_total,
    ]);
    $context['finished'] = $import_count / $import_total;
  }

  /**
   * Batch process to delete the uploaded CSV.
   *
   * @param int $file_id
   *   The file ID.
   * @param array $context
   *   The batch context.
   */
  public static function batchDeleteUploadedFile($file_id, array &$context) {
    $file_storage = \Drupal::entityTypeManager()->getStorage('file');
    /** @var \Drupal\file\FileInterface $file */
    $file = $file_storage->load($file_id);
    unlink($file->getFileUri());
    $file->delete();
    $context['message'] = t('Removing uploaded CSV.');
    $context['finished'] = 1;
  }

  /**
   * Batch finished callback: display batch statistics.
   *
   * @param bool $success
   *   Indicates whether the batch has completed successfully.
   * @param mixed[] $results
   *   The array of results gathered by the batch processing.
   * @param string[] $operations
   *   If $success is FALSE, contains the operations that remained unprocessed.
   */
  public static function finishBatch($success, array $results, array $operations) {
    if (!$success) {
      $error_operation = reset($operations);
      \Drupal::messenger()->addError(t('An error occurred while processing @operation with arguments: @args', [
        '@operation' => $error_operation[0],
        '@args' => (string) print_r($error_operation[0], TRUE),
      ]));
      return;
    }

    if (!empty($results['error'])) {
      \Drupal::messenger()->addError($results['error_message']);
    }
    else {
      if (!empty($results['import_created'])) {
        \Drupal::messenger()->addMessage(\Drupal::translation()->formatPlural(
          $results['import_created'],
          'Imported 1 price.',
          'Imported @count prices.'
        ));
      }
      if (!empty($results['import_updated'])) {
        \Drupal::messenger()->addMessage(\Drupal::translation()->formatPlural(
          $results['import_updated'],
          'Updated 1 price.',
          'Updated @count prices.'
        ));
      }
      if (!empty($results['import_skipped'])) {
        \Drupal::messenger()->addWarning(\Drupal::translation()->formatPlural(
          $results['import_skipped'],
          'Skipped 1 price during import.',
          'Skipped @count prices during import.'
        ));
      }
    }
  }

  /**
   * Builds the header mapping.
   *
   * @param array $mapping
   *   The configured column mapping.
   *
   * @return array
   *   The header mapping (real_column => mapped_column).
   */
  protected static function buildHeaderMapping(array $mapping) {
    $header_mapping = [
      $mapping['purchasable_entity_column'] => 'purchasable_entity',
      $mapping['quantity_column'] => 'quantity',
    ];

    // The list price column is optional.
    if (!empty($mapping['list_price_column'])) {
      $header_mapping += [
        $mapping['list_price_column'] => 'list_price',
      ];
    }

    $header_mapping += [
      $mapping['price_column'] => 'price',
      $mapping['currency_column'] => 'currency_code',
    ];
    return $header_mapping;
  }

  /**
   * Processes the given CSV row and price list item.
   *
   * @param array $row
   *   The CSV row to process.
   * @param \Drupal\commerce_pricelist\Entity\PriceListItemInterface $price_list_item
   *   The price list item.
   */
  protected static function processRow(array $row, PriceListItemInterface $price_list_item) {
    $currency_code = $row['currency_code'];
    // If the price is given in a format like "4 000" we should allow it.
    $row['price'] = str_replace(' ', '', $row['price']);
    $price = new Price($row['price'], $currency_code);
    $price_list_item->setPrice($price);

    if (isset($row['list_price']) && $row['list_price'] !== '') {
      $row['list_price'] = str_replace(' ', '', $row['list_price']);
      $list_price = new Price($row['list_price'], $currency_code);
      $price_list_item->setListPrice($list_price);
    }
  }

}
