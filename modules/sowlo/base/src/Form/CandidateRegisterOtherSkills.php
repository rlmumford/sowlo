<?php

namespace Drupal\sowlo_base\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Other Skills form.
 */
class CandidateRegisterOtherSkills extends CandidateRegisterProfile {

  /**
   * {@inheritdoc}
   */
  public function getProfileStep() {
    return 'other_skills';
  }

  /**
   * {@inheritdoc}
   */
  public function getProfileType() {
    return 'other_skills';
  }
}

