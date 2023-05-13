<?php

namespace Drupal\color_element\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'color_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "color_formatter",
 *   module = "color_element",
 *   label = @Translation("Color formatter"),
 *   field_types = {
 *     "color_field"
 *   }
 * )
 */
class ColorFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'color_element',
        '#color' => $this->viewValue($item),
      ];

      // Attached our CSS to the block for rendering.
      $elements[$delta]['#attached'] = [
        'library' => ['color_element/color_formatter'],
      ];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    return Html::escape($item->value);
  }

}
