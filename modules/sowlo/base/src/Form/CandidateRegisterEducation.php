<?php

namespace Drupal\sowlo_base\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Utility\Random;

/**
 * Personal info form.
 */
class CandidateRegisterEducation extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sowlow_base_candidate_register_education';
  }

  /**
   * {@inheritdoc}
   */
  protected function init(FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    $profile_indicator = 'new';
    if (!empty($cached_values['candidate_current_education_profile'])) {
      $profile_indicator = $cached_values['candidate_current_education_profile'];
    }

    if ($profile_indicator == 'new') {
      $profile_indicator = (new Random())->string(5, TRUE);
      $cached_values['candidate_education'][$profile_indicator] = $this->entityManager->getStorage('profile')->create(['type' => 'education']);
      $cached_values['candidate_current_education_profile'] = $profile_indicator;
    }
    $this->setEntity($cached_values['candidate_education'][$profile_indicator]);

    parent::init($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $cached_values['candidate_education'][$cached_values['candidate_current_education_profile']] = $this->entity;
  }
}

