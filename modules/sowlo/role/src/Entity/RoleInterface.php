<?php

namespace Drupal\sowlo_role\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for sowlo_roles.
 */
interface RoleInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets whether the sowlo_role is active.
   *
   * Unpublished sowlo_roles are only visible to their authors and administrators.
   *
   * @return bool
   *   TRUE if the sowlo_role is active, FALSE otherwise.
   */
  public function isActive();

  /**
   * Sets whether the sowlo_role is active.
   *
   * @param bool $active
   *   Whether the sowlo_role is active.
   *
   * @return $this
   */
  public function setActive($active);

  /**
   * Gets the sowlo_role creation timestamp.
   *
   * @return int
   *   The sowlo_role creation timestamp.
   */
  public function getCreatedTime();

  /**
   * Sets the sowlo_role creation timestamp.
   *
   * @param int $timestamp
   *   The sowlo_role creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

}
