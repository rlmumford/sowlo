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
class CandidateRegisterPersonal extends ContentEntityForm {

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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sowlow_base_candidate_register_personal';
  }

  /**
   * {@inheritdoc}
   */
  protected function init(FormStateInterface $form_state) {
    $cached_values = &$form_state->getTemporaryValue('wizard');
    if (empty($cached_values['candidate_personal'])) {
      $cached_values['candidate_personal'] = $this->entityManager->getStorage('profile')->create(['type' => 'individual']);
    }
    $this->setEntity($cached_values['candidate_personal']);

    parent::init($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $cached_values['candidate_personal'] = $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cached_values = &$form_state->getTemporaryValue('wizard');
    $cached_values['candidate_personal'] = $this->entity;
  }
}

