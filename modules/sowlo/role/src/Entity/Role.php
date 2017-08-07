<?php

namespace Drupal\sowlo_role\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;

/**
 * Defines the sowlo_role entity class.
 *
 * @ContentEntityType(
 *   id = "sowlo_role",
 *   label = @Translation("Role"),
 *   bundle_label = @Translation("Role"),
 *   handlers = {
 *     "storage" = "Drupal\sowlo_role\RoleStorage",
 *     "view_builder" = "Drupal\sowlo_role\RoleViewBuilder",
 *     "access" = "Drupal\sowlo_role\RoleAccessControlHandler",
 *     "permission_provider" = "Drupal\sowlo_role\RolePermissionProvider",
 *     "list_builder" = "Drupal\sowlo_role\RoleListBuilder",
 *     "form" = {
 *       "default" = "Drupal\sowlo_role\Form\RoleForm",
 *       "add" = "Drupal\sowlo_role\Form\RoleForm",
 *       "edit" = "Drupal\sowlo_role\Form\RoleForm",
 *       "delete" = "Drupal\sowlo_role\Form\RoleDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer sowlo_role",
 *   base_table = "sowlo_role",
 *   revision_table = "sowlo_role_revision",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "revision" = "revision",
 *     "uuid" = "uuid"
 *   },
 *  links = {
 *    "canonical" = "/sowlo_role/{sowlo_role}",
 *    "edit-form" = "/sowlo_role/{sowlo_role}/edit",
 *    "delete-form" = "/sowlo_role/{sowlo_role}/delete",
 *    "collection" = "/sowlo_role",
 *    "set-default" = "/sowlo_role/{sowlo_role}/set-default"
 *   },
 *   common_reference_target = TRUE,
 * )
 */
class Role extends ContentEntityBase implements RoleInterface {

  use EntityChangedTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setActive($active) {
    $this->set('status', (bool) $active);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'stirng',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['owner'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setDescription(t('The user that owns this role.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default');

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Active'))
      ->setDescription(t('Whether the sowlo_role is active.'))
      ->setDefaultValue(TRUE)
      ->setRevisionable(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the sowlo_role was created.'))
      ->setRevisionable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the sowlo_role was last edited.'))
      ->setRevisionable(TRUE);

    return $fields;
  }

}
