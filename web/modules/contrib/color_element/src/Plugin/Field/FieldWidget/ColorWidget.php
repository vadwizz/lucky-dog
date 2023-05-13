<?php

namespace Drupal\color_element\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'color_widget' widget.
 *
 * @FieldWidget(
 *   id = "color_widget",
 *   label = @Translation("Color selector"),
 *   field_types = {
 *     "color_field"
 *   }
 * )
 */
class ColorWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'color_values' => '#000000,#ffffff',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['color_values'] = [
      '#type' => 'textarea',
      '#title' => t('Color values'),
      '#description' => $this->t('Enter a list of color values that can be selected (separated by commas).'),
      '#default_value' => $this->getSetting('color_values'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Color values: @color_values', ['@color_values' => !empty($this->getSetting('color_values')) ? $this->getSetting('color_values') : 'Not set']);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $color_values = str_replace('#', '', explode(',', $this->getSetting('color_values')));
    $options = [];
    foreach ($color_values as $key => $color_value) {
      $options[$color_value] = '<div class="color-element-swatch" data-swatch-color="#' . $color_value . '"><div class="inner"></div></div>';
    }

    $element['value'] = $element + [
      '#type' => 'textfield',
      '#size' => 7,
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#attached' => [
        'library' => ['color_element/color_element_field'],
      ],
      '#suffix' => '<div class="color-element">' . implode($options) . '</div>',
    ];

    return $element;
  }

}
