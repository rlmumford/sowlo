<?php

namespace Drupal\sowlo_ranking\Plugin\RankingGame;

use Drupal\sowlo_ranking\Entity\CandidateRank;
use Drupal\sowlo_ranking\RankingGameBase;
use Drupal\sowlo_role\Entity\Role;
use Drupal\user\UserInterface;

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
    // A threshold number within which the contest will be ocuntent as a draw.
    $threshold = 10;

    $scoreA = $this->calculatePEScore($candidateA->candidate->entity, $candidateA->role->entity);
    $scoreB = $this->calculatePEScore($candidateB->candidate->entity, $candidateB->role->entity);

    if (abs($scoreA - $scoreB) < $threshold) {
      return 0.5;
    }
    else if ($scoreA > $scoreB) {
      return 1;
    }
    else {
      return 0;
    }
  }

  /**
   * Calculate a project experience score.
   *
   * @param \Drupal\user\UserInterface $candidate
   * @param \Drupal\sowlo_role\Entity\SowloRole $role
   *
   * @return integer
   */
  protected function calculatePEScore(UserInterface $candidate, Role $role) {
    $scores = [];

    $profile_ids = $this->getEntityQuery('profile')
      ->condition('type', 'experience_work')
      ->condition('uid', $candidate->id())
      ->execute();

    $first_start = $last_end = NULL;
    foreach ($this->getEntityStorage('profile')->load($profile_ids) as $profile) {
      foreach ($profile->workexp_projects as $project_item) {
        $pscore = [];
        $project = $project_item->entity;

        // Get a relevance score for the project experience.
        $pscore['rel'] = $this->calculatePERelevance($project, $role);

        // Get the time in months spent with the project.
        if (!empty($project->proj_to->value)) {
          $interval = $project->proj_to->date->diff($project->proj_from->date);
          $pscore['time'] = max($interval->m, 1);

          // Get the time ago of this prject experience.
          $age_interval = (new DateTime('now'))->diff($project->proj_to->date);
          $pscore['age'] = max($age_interval->m, 1);
        }
        else {
          $pscore['age'] = 1;

          $interval = (new DateTime('now'))->diff($project->proj_from->date);
          $pscore['time'] = $interval->m;
        }

        $scores[] = $pscore;

        if (empty($first_start) || $project->proj_from->date < $first_start) {
          $first_start = $project->proj_from->date;
        }
        if (empty($last_end) || $project->proj_to->date > $last_end) {
          $last_end = $project->proj_to->date;
        }
        if (empty($project->proj_to->value)) {
          $last_end = new DateTime('now');
        }
      }
    }

    $score = 0;
    foreach ($scores as $s) {
      $age_modifier = ($s['age'] < 60) ? 1 : (($s['age'] < 120) ? 0.4 : (($s['age'] < 180) ? 0.1 : 0));
      $score += $s['rel'] * $age_modifier * $s['time'];
    }

    // @todo: Variety modifier?
    return $score;
  }

  /**
   * Calculate the project relevance.
   *
   * A project containing all necessary respoinsibilities and skills should score .6
   * A project containing all desired responsibilities and skills should score 1
   * A project continaing all extra responsibilities and skills should score 1.2.
   */
  protected function calculatePERelevance($project, Role $role) {
    // @todo: Determine how role responsibilitities are being stored.
  }
}
