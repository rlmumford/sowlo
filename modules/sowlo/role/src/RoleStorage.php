<?php

namespace Drupal\sowlo_role;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the entity storage for sowlo_role.
 */
class RoleStorage extends SqlContentEntityStorage implements RoleStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadByUser(AccountInterface $account, $active = TRUE) {
    $result = $this->loadByProperties([
      'owner' => $account->id(),
      'status' => $active,
    ]);

    return reset($result);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleByUser(AccountInterface $account, $active = TRUE) {
    return $this->loadByProperties([
      'owner' => $account->id(),
      'status' => $active,
    ]);
  }

}
