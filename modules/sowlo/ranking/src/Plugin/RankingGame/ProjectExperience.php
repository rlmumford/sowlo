<?php

namespace Drupal\sowlo_ranking\Plugin\RankingGame;

use Drupal\sowlo_ranking\Entity\CandidateRank;
use Drupal\sowlo_ranking\RankingGameBase;

/**
 * Class for project experience ranking games.
 *
 * @RankingGame(
 *   id = "project_experience",
 *   label = @Translation("Project Experience"),
 *   global = 0,
 *   k_value = 42,
 * )
 */
class ProjectExperience extends RankingGameBase {

  /**
   * {@inheritdoc}
   */
  protected function determineWinner(CandidateRank $candidateA, CandidateRank $candidateB) {
  }
}
