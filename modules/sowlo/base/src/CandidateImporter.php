<?php

namespace Drupal\sowlo_base;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class for importing candidates.
 */
class CandidateImporter {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Get the storage controller of a given entity type.
   */
  protected function getEntityStorage($entity_type_id) {
    return $this->entityTypeManager->getStorage($entity_type_id);
  }

  /**
   * Construct a new CandidateImporter object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Import single candidate from an object
   */
  public function importSingle($data) {
    $candidate = $this->getEntityStorage('user')->create([]);
    $candidate->addRole('candidate');
    $candidate->set('status', TRUE);
    if (!empty($data->name)) {
      $candidate->name->value = $data->name;
    }
    $candidate->save();

    foreach (['individual', 'education', 'experience_work', 'other_skills'] as $profile_type) {
      if (empty($data->{$profile_type})) {
        continue;
      }

      if (is_array($data->{$profile_type})) {
        foreach ($data->{$profile_type} as $profile_data) {
          $this->importProfile($candidate, $profile_type, $profile_data);
        }
      }
      else {
        $this->importProfile($candidate, $profile_type, $data->{$profile_type});
      }
    }

    return $candidate;
  }

  /**
   * Import multiple candidates from and array of objects.
   */
  public function importMultiple(array $data = array()) {
    foreach ($data as $item) {
      $this->importSingle($item);
    }
  }

  /**
   * Import JSON.
   *
   * @param $json Data in json format.
   */
  public function importJSON($json = '') {
    $data = json_decode($json);
    if (json_last_error() == JSON_ERROR_NONE) {
      if (is_array($data)) {
        $this->importMultiple($data);
      }
      else {
        $this->importSingle($data);
      }
    }
  }

  /**
   * Import a profile.
   *
   * @param \Drupal\user\UserInterface $candidate
   * @param string $profile_type
   * @param stdClass $profile_data
   */
  public function importProfile($candidate, $profile_type, $profile_data) {
    $profile = $this->getEntityStorage('profile')->create([
      'type' => $profile_type,
    ]);
    $profile->uid->entity = $candidate;
    $profile->set('status', TRUE);

    $this->importFields($profile, $profile_data);
    $profile->save();
  }

  /**
   * Import fields.
   */
  public function importFields($entity, $data) {
    foreach ($entity->getFields(FALSE) as $field_name => $field) {
      if (empty($data->{$field_name})) {
        continue;
      }

      $field_data = $data->{$field_name};
      $definition = $field->getFieldDefinition()->getFieldStorageDefinition();
      $cardinality = $definition->getCardinality();
      if ($cardinality == 1) {
        $this->importFieldItem($field->first() ? : $field->appendItem(), $field_data);
      }
      else {
        if (is_array($field_data)) {
          foreach ($field_data as $delta => $field_data_item) {
            $this->importFieldItem(
              !$field->offsetExists($delta) ? $field->appendItem() : $field->offsetGet($delta),
              $field_data_item
            );
          }
        }
        else {
          $this->importFieldItem($field->first() ? : $field->appendItem(), $field_data);
        }
      }
    }
  }

  /**
   * Import Field.
   */
  protected function importFieldItem($field_item, $data) {
    $definition = $field_item->getFieldDefinition();
    $storage_definition = $field_item->getFieldDefinition()->getFieldStorageDefinition();
    $field_name = $definition->getName();
    if (is_object($data)) {
      if ($target_type = $storage_definition->getSetting('target_type')) {
        $target_bundles = $definition->getSetting('handler_settings')['target_bundles'];
        if (count($target_bundles) === 1) {
          $target_bundle = reset($target_bundles);
        }

        if ($target_type == 'paragraph') {
          $paragraph = $this->importParagraph($data, $target_bundle);
          $field_item->target_id = $paragraph->id();
          $field_item->target_revision_id = $paragraph->getRevisionId();
          $field_item->entity = $paragraph;
        }
        else if ($target_type == 'location') {
          $field_item->target_id = $this->importLocation($data, $target_bundle)->id();
        }
        else {
          continue;
        }
      }
      else {
        foreach ($data as $property => $value) {
          $field_item->{$property} = $value;
        }
      }
    }
    else {
      if ($target_type = $storage_definition->getSetting('target_type')) {
        if ($target_type == 'taxonomy_term' && !is_numeric($data)) {
          $vocabs = $definition->getSetting('handler_settings')['target_bundles'];
          $terms = $this->getEntityStorage('taxonomy_term')->loadByProperties(['name' => $data, 'vid' => $vocabs]);
          if (!empty($terms)) {
            $tid = reset($terms)->id();
          }
          else if (!empty($vocabs)) {
            $vid = reset($vocabs);
            $term = $this->getEntityStorage('taxonomy_term')->create(['vid' => $vid, 'name' => $data]);
            $term->save();
            $tid = $term->id();
          }

          if (!empty($tid)) {
            $field_item->target_id = $tid;
          }
        }
        else if (is_numeric($data)) {
          $field_item->target_id = $data;
        }
      }
      else {
        $main_property = $storage_definition->getMainPropertyName();
        $field_item->{$main_property} = $data;
      }
    }
  }

  /**
   * Import paragraph.
   */
  protected function importParagraph($field_data, $type = '') {
    if (!empty($field_data->type)) {
      $type = $field_data->type;
    }

    if (empty($type)) {
      return NULL;
    }

    $paragraph = $this->getEntityStorage('paragraph')->create([
      'type' => $type,
    ]);
    $data = clone $field_data;
    unset($data->type);
    $this->importFields($paragraph, $data);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Import a location.
   */
  protected function importLocation($field_data, $type = '') {
    if (!empty($field_data->type)) {
      $type = $field_data->type;
    }

    if (empty($type)) {
      return NULL;
    }

    $location = $this->getEntityStorage('location')->create([
      'type' => $type,
    ]);
    $data = clone $field_data;
    unset($data->type);
    $this->importFields($location, $data);
    $location->save();
    return $location;
  }
}
