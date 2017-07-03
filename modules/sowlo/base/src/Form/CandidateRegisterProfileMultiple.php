<?php

namespace Drupal\sowlo_base\Form;

use Drupal\Component\Utility\Random;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Personal info form.
 */
abstract class CandidateRegisterProfileMultiple extends CandidateRegisterProfile {

  /**
   * {@inheritdoc}
   */
  protected function init(FormStateInterface $form_state) {
    $cached_values = &$form_state->getTemporaryValue('wizard');

    $profile_indicator = 'new';
    $step = $this->getProfileStep();
    if (!empty($cached_values["candidate_current_{$step}__profile"])) {
      $profile_indicator = $cached_values["candidate_current_{$step}_profile"];
    }

    if ($profile_indicator == 'new') {
      $profile_indicator = (new Random())->string(5, TRUE);
      $cached_values['candidate_'.$step][$profile_indicator] = $this->entityManager->getStorage('profile')->create(['type' => $this->getProfileType()]);
      $cached_values["candidate_current_{$step}_profile"] = $profile_indicator;
    }
    $this->setEntity($cached_values['candidate_'.$step][$profile_indicator]);

    ContentEntityForm::init($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $form, FormStateInterface $form_state) {
    ContentEntityForm::submitForm($form, $form_state);
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $step = $this->getProfileStep();
    $cached_values['candidate_'.$step][$cached_values["candidate_current_{$step}_profile"]] = $this->entity;
  }

  /**
   * Add another submit.
   */
  public function submitFormAddAnother(array $form, FormStateInterface $form_state) {
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $step = $this->getProfileStep();
    $cached_values["candidate_current_{$step}_profile"] = 'new';
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $step = $this->getProfileStep();
    $cached_values['candidate_'.$step][$cached_values["candidate_current_{$step}_profile"]] = $this->entity;
  }
}

