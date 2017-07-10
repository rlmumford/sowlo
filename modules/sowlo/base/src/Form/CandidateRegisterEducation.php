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

  /**
   * {@inheritdoc}
   */
  public function buildProfileSummaryTableHeader() {
    $row = [
      'institution' => $this->t('Institution'),
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

    $values = $profile->toArray();
    $institution = $values['education_institution'][0]['entity']->name->value;
    $row['institution']['data'] = $institution;

    $start = $profile->education_from->view([
      'type' => 'datetime_custom',
      'label' => 'hidden',
      'settings' => [
        'date_format' => 'F Y',
      ],
    ]);
    $end = $profile->education_to->view([
      'type' => 'datetime_custom',
      'label' => 'hidden',
      'settings' => [
        'date_format' => 'F Y',
      ],
    ]);
    $row['date']['data'] = $this->t(
      '@start to @end',
      [
        '@start' => render($start),
        '@end' => render($end),
      ]
    );

    $row += parent::buildProfileSummaryTableRow($profile, $indicator, $is_current);
    return $row;
  }
}

