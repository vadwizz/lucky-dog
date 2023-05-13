<?php

namespace Drupal\mmenu\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Serialization\Yaml;

/**
 * Class MmenuSettingsForm.
 *
 * @package Drupal\mmenu\Form
 *
 * @ingroup mmenu
 */
class MmenuSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'mmenu_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $mmenu_name = '') {
    
    \Drupal::messenger()->addWarning('Caution: most of settings here are currently disabled. See README');
    
    $mmenu = mmenu_list($mmenu_name);

    $form['general'] = array(
      '#tree' => TRUE,
      '#type' => 'details',
      '#title' => t('General'),
      '#weight' => -5,
      '#open' => TRUE,
    );

    $form['general']['enabled'] = array(
      '#title' => t('Enabled?'),
      '#type' => 'select',
      '#options' => array(
        1 => t('Yes'),
        0 => t('No'),
      ),
      '#default_value' => $mmenu['enabled'] ? 1 : 0,
      '#required' => TRUE,
      '#weight' => -3,
      '#description' => t('Enable or disable the mmenu.'),
    );

    $form['general']['name'] = array(
      '#type' => 'hidden',
      '#value' => $mmenu_name,
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 0,
    );
    $form['actions']['reset'] = array(
      '#type' => 'submit',
      '#value' => t('Reset'),
      '#weight' => 1,
    );
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    switch ($values['op']->__toString()) {
      case t('Save'):

        $config = \Drupal::configFactory()->getEditable('mmenu.settings');
        $config->set('mmenu_item_' . $values['general']['name'], $mmenu);
        $config->save();

        // Clears mmenus cache.
        \Drupal::cache()->delete('mmenus:cache');
        
        \Drupal::messenger()->addWarning('The settings have been saved.');
        break;

      case t('Reset'):
        // Deletes the mmenu settings from database.
        $config = \Drupal::configFactory()->getEditable('mmenu.settings');
        $config->delete('mmenu_item_' . $values['general']['name']);

        // Clears mmenus cache.
        \Drupal::cache()->delete('mmenus:cache');
        
        \Drupal::messenger()->addWarning('The settings have been reset.');
        
        break;
    }
  }
}
