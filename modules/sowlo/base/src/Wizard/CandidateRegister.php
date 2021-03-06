<?php

namespace Drupal\sowlo_base\Wizard;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Wizard\FormWizardBase;
use Drupal\sowlo_base\Form\CandidateRegisterProfileMultiple;

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

  public function getTempstore() {
    $session = \Drupal::service('session');
    if (!$session->isStarted()) {
      $session->set('registering_candidate', TRUE);
      $session->start();
    }
    return \Drupal::service('user.private_tempstore')->get($this->getTempstoreId());
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
      'other_skills' => [
        'form' => 'Drupal\sowlo_base\Form\CandidateRegisterOtherSkills',
        'title' => $this->t('Additional Skills'),
      ],
      'review' => [
        'form' => 'Drupal\sowlo_base\Form\CandidateRegisterPreview',
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $el = $form_state->getTriggeringElement();
    if (in_array($el['#op'], ['next','add_another'])) {
      $cached_values = $form_state->getTemporaryValue('wizard');
      if ($form_state->hasValue('label')) {
        $cached_values['label'] = $form_state->getValue('label');
      }
      if ($form_state->hasValue('id')) {
        $cached_values['id'] = $form_state->getValue('id');
      }
      if (is_null($this->machine_name) && !empty($cached_values['id'])) {
        $this->machine_name = $cached_values['id'];
      }
      $this->getTempstore()->set($this->getMachineName(), $cached_values);

      if ($el['#op'] == 'next' && !$form_state->get('ajax')) {
        $form_state->setRedirect($this->getRouteName(), $this->getNextParameters($cached_values));
      }
      else if ($el['#op'] == 'add_another') {
        $form_state->setRedirect($this->getRouteName(),[
          'machine_name' => $this->getMachineName(),
          'step' => $this->getStep($cached_values),
          'js' => 'nojs',
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(FormInterface $form_object, FormStateInterface $form_state) {
    $actions = parent::actions($form_object, $form_state);
    array_unshift($actions['submit']['#validate'], '::storeOperation');
    $actions['#weight'] = 1000;

    $actions['submit']['#op'] = 'next';

    if ($form_object instanceof CandidateRegisterProfileMultiple) {
      $add_another_submit = $actions['submit']['#submit'];
      array_pop($add_another_submit);
      $add_another_submit[] = [$form_object, 'submitFormAddAnother'];
      $add_another_submit[] = '::submitForm';
      $actions['add_another'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add Another @type', ['@type' => ($this->step == 'work') ? 'Job' : 'School']),
        '#submit' => $add_another_submit,
        '#validate' => $actions['submit']['#validate'],
        '#weight' => -5,
        '#op' => 'add_another',
      ];
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function finish(array &$form, FormStateInterface $form_state) {
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $user = $cached_values['candidate_user'];
    $user->addRole('candidate');
    $user->save();

    foreach ($cached_values as $key => $value) {
      if (substr($key, 0, 10) != 'candidate_') {
        continue;
      }

      if ($key == 'candidate_user') {
        continue;
      }

      if (is_array($value)) {
        foreach ($value as $profile) {
          $profile->setOwner($user);
          $profile->save();
        }
      }
      else if (is_object($value)) {
        $value->setOwner($user);
        $value->save();
      }
    }
    $user->save();

    parent::finish($form, $form_state);
  }
}
