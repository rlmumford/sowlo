<?php

namespace Drupal\sowlo_base\Wizard;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;

class CandidateRegister extends FormWizardBase {

  /**
   * {@inheritdoc}
   */
  public function getWizardLabel() {
    return $this->t('Candidate Register');
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineLabel() {
    return $this->t('Condidate Register Name');
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($cached_values) {
    return [
      'registration' => [
        'form' => 'Drupal\sowlo_base\Form\CandidateRegisterRegister',
        'title' => $this->t('Register'),
      ],
      'personal' => [
        'form' => 'Drupal\sowlo_base\Form\CandidateRegisterPersonal',
        'title' => $this->t('Personal'),
      ],
      'work' => [
        'form' => 'Drupal\sowlo_base\Form\CandidateRegisterWork',
        'title' => $this->t('Work Experience'),
      ],
      'education' => [
        'form' => 'Drupal\sowlo_base\Form\CandidateRegisterEducation',
        'title' => $this->t('Education'),
      ],
      'review' => [
        'form' => 'Drupal\sowlow_base\Form\CandidateRegisterReview',
        'title' => $this->t('Review'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return 'sowlo_base.candidate.register.step';
  }

  /**
   * Process form callback.
   */
  public function processForm($element, FormStateInterface $form_state, $form) {
    return $element;
  }

  /**
   * Store operation.
   */
  public function storeOperation(array $form, FormStateInterface $form_state) {
    $op = $form_state->getValue('op');
    $form_state->set('op', $op);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $form, FormStateInterface $form_state) {
    $form_state->setValue('op', $form_state->get('op'));
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(FormInterface $form_object, FormStateInterface $form_state) {
    $actions = parent::actions($form_object, $form_state);
    array_unshift($actions['submit']['#validate'], '::storeOperation');
    $actions['#weight'] = 1000;
    return $actions;
  }
}
