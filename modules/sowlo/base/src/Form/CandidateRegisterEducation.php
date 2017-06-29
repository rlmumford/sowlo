<?php

namespace Drupal\sowlo_base\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Personal info form.
 */
class CandidateRegisterEducation extends CandidateRegisterProfileMultiple {

  /**
   * {@inheritdoc}
   */
  public function getProfileStep() {
    return 'education';
  }

  /**
   * {@inheritdoc}
   */
  public function getProfileType() {
    return 'education';
  }
}

