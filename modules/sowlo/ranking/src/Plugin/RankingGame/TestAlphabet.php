<?php

namespace Drupal\sowlo_ranking\Plugin\RankingGame;

use Drupal\sowlo_ranking\Entity\CandidateRank;
use Drupal\sowlo_ranking\RankingGameBase;

/**
 * Class for project experience ranking games.
 *
 * @RankingGame(
 *   id = "test_alphabet",
 *   label = @Translation("Test Alphabet"),
 *   global = 1,
 *   k_value = 42,
 * )
 */
class TestAlphabet extends RankingGameBase {

  /**
   * {@inheritdoc}
   */
  protected function determineWinner(CandidateRank $candidateA, CandidateRank $candidateB) {
    $first_letter_a = substr($candidateA->candidate->entity->label(), 0, 1);
    $first_letter_b = substr($candidateB->candidate->entity->label(), 0, 1);

    if ($first_letter_a == $first_letter_b) {
      return 0.5;
    }
    if ($first_letter_a > $first_letter_b) {
      return 1;
    }
    return 0;
  }
}
