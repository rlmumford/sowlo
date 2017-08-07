<?php

namespace Drupal\sowlo_ranking;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\sowlo_ranking\Entity\CandidateRank;
use Drupal\sowlo_ranking\RankingGameInterface;
use Drupal\sowlo_role\Entity\Role;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Base class for ranking game plugins.
 */
abstract class RankingGameBase extends PluginBase implements RankingGameInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection;
   */
  protected $databaseConnection;

  /**
   * Create instance of Ranking Game plugins.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Constructs a new RankingGameBase object.
   *
   * @param
   */
  public function __construct(EntityTypeManager $entity_type_manager, Connection $connection, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->databaseConnection = $connection;
  }

  /**
   * Determine the winner of the game.
   *
   * @param \Drupal\sowlo_ranking\Entity\CandidateRank $candidateA
   * @param \Drupal\sowlo_ranking\Entity\CandidateRank $candidateB
   *
   * @return 1 if A wins, 0 if B wins, 0.5 if draw.
   */
  abstract protected function determineWinner(CandidateRank $candidateA, CandidateRank $candidateB);

  /**
   * {@inheritdoc}
   */
  public function game(CandidateRank $candidateA, CandidateRank $candidateB) {
    $ra = pow(10, ($candidateA->rank->value/400));
    $rb = pow(10, ($candidateB->rank->value/400));

    $ea = $ra / ($ra + $rb);
    $eb = $rb / ($ra + $rb);

    $sa = $this->determineWinner($candidateA, $candidateB);
    $sb = 1 - $sa;

    $k = $this->getPluginDefinition()['k_value'];
    $candidateA->rank->value = $candidateA->rank->value + ($k * ($sa - $ea));
    $candidateB->rank->value = $candidateB->rank->value + ($k * ($sb - $eb));

    $candidateA->save();
    $candidateB->save();
  }

  /**
   * {@inheritdoc}
   */
  public function spoolGamesOnNewRole(Role $role) {
    // If this is a global ranking then nothing needs to happen on a new role.
    if ($this->getPluginDefinition()['global']) {
      return;
    }

    // Get the existing candidate ranks for this role.
    $query = $this->getEntityQuery('candidate_rank');
    $query->condition('role.target_id', $role->id());
    $query->condition('category', $this->getPluginId());
    $rank_ids = $query->execute();

    $user_ids = $this->databaseConnection->select('candidate_rank', 'c')
      ->condition('c.id', $rank_ids)
      ->fields('c', ['candidate'])
      ->execute()->fetchCol();

    $query = $this->getEntityQuery('user');
    $query->condition('uid', $user_ids, 'NOT IN');
    $query->condition('status', 1);
    $query->condition('roles', 'candidate');

    // @todo: Limit the users we select so that we don't waste resources on
    // unnessacry rankings.

    $pairs = [];
    $rank_storage = $this->getEntityStorage('candidate_rank');
    foreach ($query->execute() as $candidate_id) {
      $candidate_rank = $rank_storage->create([]);
      $candidate_rank->candidate->target_id = $candidate_id;
      $candidate_rank->role->target_id = $role->id();
      $candidate_rank->category->value = $this->getPluginId();
      $candidate_rank->rank->value = 200;
      $candidate_rank->name->value = $this->getPluginId()."--".$role->id()."--".$candidate_id;
      $candidate_rank->save();

      foreach ($rank_ids as $rank_id) {
        $pairs[] = [$candidate_rank->id(), $rank_id];
      }

      $rank_ids[] = $candidate_rank->id();
    }

    $this->spool($pairs);
  }

  /**
   * {@inheritdoc}
   */
  public function spoolGamesOnNewCandidate(UserInterface $candidate) {
    if ($this->getPluginDefinition()['global']) {
      $rank_ids = $this->getEntityQuery('candidate_rank')
        ->condition('category', $this->getPluginId())
        ->condition('candidate', $candidate->id())
        ->execute();

      if (empty($rank_ids)) {
        $other_rank_ids = $this->getEntityQuery('candidate_rank')
          ->condition('category', $this->getPluginId())
          ->execute();

        $candidate_rank = $this->getEntityStorage('candidate_rank')->create([]);
        $candidate_rank->candidate->target_id = $candidate->id();
        $candidate_rank->category->value = $this->getPluginId();
        $candidate_rank->rank->value = 200;
        $candidate_rank->name->value = $this->getPluginId()."----".$candidate->id();
        $candidate_rank->save();

        $pairs = [];
        foreach ($other_rank_ids as $id) {
          $pairs[] = [$candidate_rank->id(), $id];
        }

        $this->spool($pairs);
      }

      return;
    }

    // Get the applicable roles.
    // @todo: Limit this result as to not waste resources.
    $role_ids = $this->getEntityQuery('sowlo_role')
      ->execute();

    $pairs = [];
    foreach ($role_ids as $role_id) {
      $rank_id = $this->getEntityQuery('candidate_rank')
        ->condition('category', $this->getPluginId())
        ->condition('candidate', $candidate->id())
        ->condition('role', $role_id)
        ->execute();

      if (!empty($rank_id)) {
        continue;
      }

      $other_rank_ids = $this->getEntityQuery('candidate_rank')
        ->condition('category', $this->getPluginId())
        ->condition('role', $role_id)
        ->execute();
      $candidate_rank = $this->getEntityStorage('candidate_rank')->create([]);
      $candidate_rank->candidate->target_id = $candidate->id();
      $candidate_rank->role->target_id = $role_id;
      $candidate_rank->category->value = $this->getPluginId();
      $candidate_rank->rank->value = 200;
      $candidate_rank->name->value = $this->getPluginId()."--".$role_id."--".$candidate->id();
      $candidate_rank->save();

      foreach ($other_rank_ids as $other_rank_id) {
        $pairs[] = [$candidate_rank->id(), $other_rank_id];
      }
    }

    $this->spool($pairs);
  }

  /**
   * Add pairs to spool.
   *
   * @var array $pairs
   */
  protected function spool(array $pairs) {
    if (empty($pairs)) {
      return TRUE;
    }

    $query = $this->databaseConnection->insert('sowlo_ranking_spool');
    $query->fields(['idA', 'idB', 'handler', 'queued']);

    foreach ($pairs as $pair) {
      list($a, $b) = $pair;

      $query->values([
        'idA' => $a,
        'idB' => $b,
        'handler' => $this->getPluginId(),
        'queued' => REQUEST_TIME,
      ]);
    }
    $query->execute();
  }

  /**
   * Get a query object for a given entity type.
   *
   * @param string $entity_type_id
   *   The entity type.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A query object to use.
   */
  protected function getEntityQuery($entity_type_id) {
    return $this->getEntityStorage($entity_type_id)->getQuery();
  }

  /**
   * Get the storage controller for an entity type.
   *
   * @param string $entity_type_id
   *   The entity type.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The strorage controller.
   */
  protected function getEntityStorage($entity_type_id) {
    return $this->entityTypeManager->getStorage($entity_type_id);
  }

}
