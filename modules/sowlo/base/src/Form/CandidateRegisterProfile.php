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
 * Personal info form.
 */
abstract class CandidateRegisterProfile extends ContentEntityForm {

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, ModuleHandlerInterface $module_handler = NULL) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->moduleHandler = $module_handler ? : \Drupal::service('module_handler');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('module_handler')
    );
  }

  /**
   * Get the name of the profile type/step.
   */
  abstract public function getProfileStep();

  /**
   * Get the profile type.
   */
  abstract public function getProfileType();

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sowlow_base_candidate_register_'.$this->getProfileStep();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $step = $this->getProfileStep();
    if (empty($cached_values['candidate_'.$step])) {
      $cached_values['candidate_'.$step] = $this->entityManager->getStorage('profile')->create(['type' => $this->getProfileType()]);
    }
    $this->setEntity($cached_values['candidate_'.$step]);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $step = $this->getProfileStep();
    $cached_values['candidate_'.$step] = $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $step = $this->getProfileStep();
    $cached_values['candidate_'.$step] = $this->entity;
  }
}

