<?php

namespace Drupal\sowlo_role;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity\EntityAccessControlHandler as EntityApiAccessControlHandler;
use Drupal\sowlo_role\Entity\RoleType;

/**
 * Defines the access control handler for the sowlo_role entity type.
 *
 * @see \Drupal\sowlo_role\Entity\Role
 */
class RoleAccessControlHandler extends EntityApiAccessControlHandler {
}
