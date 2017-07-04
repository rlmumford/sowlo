<?php

namespace Drupal\sowlo_base\Form;

use Drupal\Component\Utility\Random;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityInterface;
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Build a summary table of profiles relevant to this step.
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $step = $this->getProfileStep();
    $profile_indicator = $cached_values["candidate_current_{$step}_profile"];
    $form['profiles_summary'] = $this->buildProfileSummaryTable($cached_values["candidate_{$step}"], $profile_indicator);

    return $form;
  }

  /**
   * Build a summary table of other profiles of this type.
   *
   * @param Profile[] $profiles
   * @param string $current
   *
   * @return array
   */
  public function buildProfileSummaryTable($profiles, $current) {
    $build = array(
      '#type' => 'table',
      '#header' => $this->buildProfileSummaryTableHeader(),
      '#title' => $this->getProfileSummaryTableTitle(),
      '#rows' => [],
      '#empty' => $this->t('There are no items yet.'),
    );

    foreach ($profiles as $key => $profile) {
      if ($row = $this->buildProfileSummaryTableRow($profile, $key, ($current == $key))) {
        $build['#rows'][$key] = $row;
      }
    }

    return $build;
  }

  /**
   * Build the table headers for the summary table.
   */
  public function buildProfileSummaryTableHeader() {
    $row['operations'] = $this->t('Operations');
    return $row;
  }

  /**
   * Get the title for the table.
   */
  public function getProfileSummaryTableTitle() {
    return $this->t('Other @step', ['@step' => ucfirst($this->getProfileStep())]);
  }

  /**
   * Build a table row.
   */
  public function buildProfileSummaryTableRow(EntityInterface $profile, $indicator, $is_current = FALSE) {
    $row = [];
    $row['operations']['data'] = [
      '#type' => 'container',
      'edit' => [
        '#type' => 'submit',
        '#value' => $this->t('Edit'),
        '#indicator' => $indicator,
        '#submit' => array(
          [$this, 'submitFormSwitchProfile'],
        ),
        '#validate' => [],
        '#access' => !$is_current,
      ],
      'remove' => [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#indicator' => $indicator,
        '#submit' => [
          [$this, 'submitFormRemoveProfile'],
        ],
        '#validate' => [],
        '#access' => !$is_current,
      ],
    ];
    return $row;
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

