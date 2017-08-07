<?php

namespace Drupal\sowlo_role;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface for sowlo_role entity storage.
 */
interface RoleStorageInterface extends EntityStorageInterface {

  /**
   * Loads the given user's sowlo_role.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *    The user entity.
   * @param bool $active
   *    Boolean representing if sowlo_role active or not.
   *
   * @return \Drupal\sowlo_role\Entity\RoleInterface
   *    The loaded sowlo_role entity.
   */
  public function loadByUser(AccountInterface $account, $active);

  /**
   * Loads the given user's sowlo_roles.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *    The user entity.
   * @param bool $active
   *    Boolean representing if sowlo_role active or not.
   *
   * @return \Drupal\sowlo_role\Entity\RoleInterface[]
   *    An array of loaded sowlo_role entities.
   */
  public function loadMultipleByUser(AccountInterface $account, $active);

}
