<?php

namespace Drupal\sowlo_base\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CandidateRegisterPreview extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Create new CandidateRegisterPreview Form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *  Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get the view builder.
   */
  public function viewBuilder() {
    return $this->entityTypeManager->getViewBuilder('profile');
  }

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
    $cached_values = &$form_state->getTemporaryValue('wizard');

    $personal_profile = $cached_values['candidate_personal'];
    if ($personal_profile->indiv_name) {
      $form['cache'] = ['max-age' => 0];
      $form['personal'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Personal Details'),
        'name' => $personal_profile->indiv_name->view(['type' => 'name']),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $form, FormStateInterface $form_state) {
  }
}
