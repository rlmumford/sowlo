<?php

namespace Drupal\sowlo_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;

class TermImportForm extends FormBase {

  /**
   * Get the list of term bundles to be imported.
   *
   * @return array
   *   Array of term bundle labels keyed by bundle.
   */
  protected function getTermBundles() {
    $term_bundles = [
      'education_institutions' => t('Educational Institutions'),
      'education_qualifications' => t('Educational Qualifications'),
      'industries' => t('Industries'),
      'responsibilities' => t('Responsibilities'),
      'skills' => t('Skills'),
    ];

    return $term_bundles;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sowlo_test.term_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['help'] = [
      '#type' => 'markup',
      '#markup' => t('Use this form to quickly add term items. Enter one item per line.'),
      '#weight' => -5,
    ];

    foreach ($this->getTermBundles() as $bundle => $label) {
      $form[$bundle.'_terms'] = [
        '#type' => 'textarea',
        '#title' => $label,
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 100,
      'submit' => [
        '#type' => 'submit',
        '#value' => t('Create Terms'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($this->getTermBundles() as $bundle => $label) {
      $input = $form_state->getValue([$bundle.'_terms']);

      if (empty($input)) {
        continue;
      }

      $lines = explode("\n", $input);
      $lines = array_map('trim', $lines);
      $lines = array_filter($lines);

      foreach ($lines as $line) {
        $tids = \Drupal::entityQuery('taxonomy_term')
          ->condition('vid', $bundle)
          ->condition('name', $line)
          ->execute();

        if (empty($tids)) {
          Term::create([
            'vid' => $bundle,
            'name' => $line,
          ])->save();
        }
      }
    }
  }
}
