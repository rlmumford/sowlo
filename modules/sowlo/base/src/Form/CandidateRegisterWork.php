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
class CandidateRegisterWork extends CandidateRegisterProfileMultiple {

  /**
   * {@inheritdoc}
   */
  public function getProfileStep() {
    return 'work';
  }

  /**
   * {@inheritdoc}
   */
  public function getProfileType() {
    return 'experience_work';
  }

  /**
   * {@inheritdoc}
   */
  public function buildProfileSummaryTableHeader() {
    $row = [
      'job' => $this->t('Job'),
      'date' => $this->t('Date'),
    ];
    $row += parent::buildProfileSummaryTableHeader();
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildProfileSummaryTableRow($profile, $indicator, $is_current = FALSE) {
    if ($is_current) {
      return FALSE;
    }

    $row['job']['data'] = $this->t(
      '@job at @company',
      [
        '@job' => $profile->workexp_job_title->value,
        '@company' => 'Company',
      ]
    );

    $row['date']['data'] = $this->t(
      '@start to @end',
      [
        '@start' => 'Now',
        '@end' => 'Now',
      ]
    );
    dpm($profile);

    $row += parent::buildProfileSummaryTableRow($profile, $indicator, $is_current);
    return $row;
  }
}
