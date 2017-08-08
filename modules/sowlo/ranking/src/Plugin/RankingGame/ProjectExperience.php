<?php

namespace Drupal\sowlo_ranking\Plugin\RankingGame;

use Drupal\sowlo_ranking\Entity\CandidateRank;
use Drupal\sowlo_ranking\RankingGameBase;
use Drupal\sowlo_role\Entity\Role;
use Drupal\user\UserInterface;
use Drupal\taxonomy\TermInterface;

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
    $scoreA = $this->calculatePEScore($candidateA->candidate->entity, $candidateA->role->entity);
    $scoreB = $this->calculatePEScore($candidateB->candidate->entity, $candidateB->role->entity);

    if ($scoreA == RankingGameBase::GAME_INVALID || $scoreB == RankingGameBase::GAME_INVALID) {
      return RankingGameBase::GAME_INVALID;
    }

    // If the scores are within 5% of eachother call it a draw.
    $threshold = (($scoreA + $scoreB)/2) * 0.05;
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

    // Load all work profiles dor this candidate.
    $profile_ids = $this->getEntityQuery('profile')
      ->condition('type', 'experience_work')
      ->condition('uid', $candidate->id())
      ->execute();

    // Build an array of essential skills.
    $essential_reqs = [];
    foreach ($role->essential_req as $ess_req_item) {
      $bundle = $ess_req_item->entity->bundle();
      if (in_array($bundle, ['role_req_skill', 'role_req_responsibility'])) {
        $essential_reqs[$ess_req_item->entity->{$bundle}->entity->id()] = 0;
      }
    }

    // Loop over all project experiences and calculate a cumulative score.
    $first_start = $last_end = NULL;
    foreach ($this->getEntityStorage('profile')->load($profile_ids) as $profile) {
      foreach ($profile->workexp_projects as $project_item) {
        $pscore = [];
        $project = $project_item->entity;

        // Get a relevance score for the project experience.
        $pscore['rel'] = $this->calculatePERelevance($project, $role, $essential_reqs);

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

    // Check that all essential requirements have been filled.
    foreach ($essential_requirements as $id => $score) {
      if (!($score > 0)) {
        $this->excludeCandidate($candidate, $role, "the candidate does not have all the essential experience.");
        return RankingGameBase::GAME_INVALID;
      }
    }

    $score = 0;
    foreach ($scores as $s) {
      $age_modifier = ($s['age'] < 60) ? 1 : (($s['age'] < 120) ? 0.4 : (($s['age'] < 180) ? 0.1 : 0));
      $score += $s['rel'] * $age_modifier * ($s['time']/6);
    }

    // @todo: Variety modifier?
    return $score;
  }

  /**
   * Calculate the project relevance.
   *
   * A project containing all necessary respoinsibilities and skills should score .8
   * A project containing all desired responsibilities and skills should score 1
   * A project continaing all extra responsibilities and skills should score 1.1.
   */
  protected function calculatePERelevance($project, Role $role, array &$essentials = array()) {
    // @todo: Determine how role responsibilitities are being stored.
    $score = 0;
    $available_scores = [
      'essential' => 0.8,
      'important' => 0.2,
      'bonus' => 0.1,
    ];

    foreach ($available_scores as $level => $available_score) {
      $er_field = "{$level}_req";
      $skills = $resps = [];
      foreach ($role->{$er_field} as $req_item) {
        $req = $req_item->entity;
        switch ($req->bundle()) {
          case 'role_req_responsibility':
            $resps[] = [
              'term' => $req->role_req_responsibility->entity,
              'score' => 0,
            ];
            break;
          case 'role_req_skill':
            $skills[] = [
              'term' => $req->role_req_skill->entity,
              'min_level' => $req->role_req_skill_level->value,
              'score' => 0,
            ];
        }
      }

      // The score per requirement ($spr) is the score available for this level
      // of requirement divided by the number of requirements in play.
      // (Education requirements are handled in a different game.
      $total_req = count($resp) + count($skills);
      $spr = $available_score / $total_req;

      foreach ($resps as $resp) {
        foreach ($project->proj_responsibilities as $presp_item) {
          $rscore = $this->calculateTermSimilarity($resp['term'], $presp_item->entity->role_req_responsibility->entity);
          if ($rscore > $resp['score']) {
            $resp['score'] = $rscore;
          }
        }

        // Set the score in the essentials array so that we can track whether
        // all "essential" skills and responsibilities have been met.
        if (isset($essentials[$resp['term']->id()]) && ($resp['score'] > $essentials[$resp['term']->id()])) {
          $essentials[$resp['term']->id()] = $resp['score'];
        }

        $score += $resp['score'] * $spr;
      }

      foreach ($skills as $skill) {
        foreach ($project->proj_skills as $pskill_item) {
          $req_slevel = $skill['min_level'];
          $slevel = $pskill_item->entity->role_req_skill_level->value;

          // Modify the score for each skill by the skill level available.
          // If the skill level is there then the modifier is 1. If the skill
          // level is lower then the modifier drops to 0.5 or 0.2.
          $level_modifier = 1;
          if ($slevel == 'proficient') {
            if ($req_slevel == 'expert') {
              $level_modifier = 0.5;
            }
          }
          else if ($slevel == 'basic') {
            if ($req_slevel == 'expert') {
              $level_modifier = 0.2;
            }
            else if ($req_slevel == 'proficient') {
              $level_modifier = 0.5;
            }
          }

          // Work out the similarity score between the skill term and the term
          // referenced by the requirement.
          $rscore = $this->calculateTermSimilarity($skill['term'], $pskill_item->entity->role_req_skill->entity);
          if (($rscore * $level_modifier) > $skill['score']) {
            $skill['score'] = ($rscore * $level_modifier);
          }
        }

        // Set the score in the essentials array so that we can track whether
        // all "essential" skills and responsibilities have been met.
        if (isset($essentials[$skill['term']->id()]) && ($skill['score'] > $essentials[$skill['term']->id()])) {
          $essentials[$skill['term']->id()] = $skill['score'];
        }

        $score += $skill['score'] * $spr;
      }
    }

    return $score;
  }

  /**
   * Calculate term similarity.
   *
   * @param \Drupal\taxonomy\TermInterface $termA
   * @param \Drupal\taxonomy\TermInterface $termB
   *
   * @return int
   *   A number between 0 and 1 representing the similarity.
   */
  protected function calculateTermSimilarity(TermInterface $termA, TermInterface $termB) {
    if ($termA->id() == $termB->id()) {
      return 1;
    }

    if ($termA->label() == $termB->label()) {
      return 1;
    }

    // @todo: Return a number between 0 and 1 for similar but non-identical
    // terms

    return 0;
  }
}
