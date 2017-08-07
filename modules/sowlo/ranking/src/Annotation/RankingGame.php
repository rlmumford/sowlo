<?php

namespace Drupal\sowlo_ranking\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an Ranking Game annotation object.
 *
 * Plugin Namespace: Plugin\RankingGame
 */
class RankingGame extends Plugin {

  /**
   * The plugin id.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the action plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * True if this doesn't depend on the role.
   */
  public $global;

  /**
   * The k value of the ELO calculation
   *
   * @var float
   */
  public $k_value = 42;

}
