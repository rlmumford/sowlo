<?php

namespace Drupal\sowlo_company\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines a company entity.
 *
 * @ContentEntityType(
 *   id = "company",
 *   label = @Translation("Company"),
 *   label_singular = @Translation("company"),
 *   label_plural = @Translation("companies"),
 *   label_count = @PluralTranslation(
 *     singular = "@count company",
 *     plural = "@count companies",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\sowlo_company\CompanyAccessControlHandler",
 *     "list_builder" = "Drupal\sowlo_company\CompanyListBuilder",
 *     "form" = {
 *       "default" = "Drupal\sowlo_company\CompanyForm"
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData"
 *   },
 *   base_table = "company",
 *   data_table = "company_data",
 *   revision_table = "company_revision",
 *   revision_data_table = "company_revision_data",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "revision" = "vid",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/company/{company}",
 *     "version-history" = "/company/{company}/history",
 *   }
 * )
 */
class Company extends ContentEntityBase {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Trading Name'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['industries'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Industries'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => 'industries',
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete_tags',
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['admin'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Administrator'))
      ->setSetting('target_type', 'user')
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }
}
