<?php

namespace Drupal\sowlo_ranking;

use Drupal\sowlo_ranking\Entity\CandidateRank;
use Drupal\sowlo_role\Entity\Role;
use Drupal\user\UserInterface;

/**
 * Interface for RankingGame handlers
 */
interface RankingGameInterface {

  /**
   * Perform a game and determine a winner.
   *
   * @param \Drupal\sowlo_ranking\Entity\CandidateRank $candidateA;
   * @param \Drupal\sowlo_ranking\Entity\CandidateRank $candidateB;
   *
   * @return \Drupal\sowlo_ranking\Entity\CandidateRank
   *   The winning rank.
   */
  public function game(CandidateRank $candidateA, CandidateRank $candidateB);

  /**
   * Spool games when a new role is added.
   *
   * @param \Drupal\sowlo_role\Entity\Role $role
   *   The role that has just been created.
   */
  public function spoolGamesOnNewRole(Role $role);

  /**
   * Spool games when a nerw candidate is added.
   *
   * @param \Drupal\user\UserInterface $candidate
   *   The candidate that has just been added to the system.
   */
  public function spoolGamesOnNewCandidate(UserInterface $candidate);
}
