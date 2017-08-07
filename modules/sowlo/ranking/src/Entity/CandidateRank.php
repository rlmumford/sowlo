<?php

namespace Drupal\sowlo_ranking\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines a rank entity.
 *
 * @ContentEntityType(
 *   id = "candidate_rank",
 *   label = @Translation("Rank"),
 *   label_singular = @Translation("rank"),
 *   label_plural = @Translation("ranks"),
 *   label_count = @PluralTranslation(
 *     singular = "@count rank",
 *     plural = "@count ranks",
 *   ),
 *   handlers = {},
 *   base_table = "candidate_rank",
 *   data_table = "candidate_rank_data",
 *   revision_table = "candidate_rank_revision",
 *   revision_data_table = "candidate_rank_revision_data",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "revision" = "vid",
 *     "uuid" = "uuid"
 *   },
 *   links = {}
 * )
 */
class CandidateRank extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setRequired(TRUE)
      ->setRevisionable(FALSE)
      ->setSetting('max_length', 255);

    $fields['candidate'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Candidate'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE)
      ->setRevisionable(FALSE);

    $fields['role'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Role'))
      ->setSetting('target_type', 'sowlo_role')
      ->setRevisionable(FALSE);

    $fields['category'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Category'))
      ->setRequired(TRUE)
      ->setRevisionable(FALSE);

    $fields['rank'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Rank'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('min', 0);

    $fields['log'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Message'))
      ->setRevisionable(TRUE);

    return $fields;
  }
}
