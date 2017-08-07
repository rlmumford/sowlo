<?php

namespace Drupal\sowlo_role;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for sowlo_role entities.
 */
class RoleViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $defaults = parent::getBuildDefaults($entity, $view_mode);
    $defaults['#theme'] = 'sowlo_role';
    return $defaults;
  }

}
