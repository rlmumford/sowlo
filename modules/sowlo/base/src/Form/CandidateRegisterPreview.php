<?php

namespace Drupal\sowlo_base\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CadidateRegisterPreview extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sowlow_base_candidate_register_preview';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['markup'] = [
      '#markup' => 'TEST',
    ];
    return $form;
  }

}
