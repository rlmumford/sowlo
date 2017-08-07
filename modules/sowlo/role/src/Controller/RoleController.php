<?php

namespace Drupal\sowlo_role\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\sowlo_role\Entity\RoleInterface;
use Drupal\sowlo_role\Entity\RoleTypeInterface;
use Drupal\sowlo_role\Entity\Role;
use Drupal\user\UserInterface;

/**
 * Returns responses for RoleController routes.
 */
class RoleController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Provides the sowlo_role submission form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\sowlo_role\Entity\RoleTypeInterface $sowlo_role_type
   *   The sowlo_role type entity for the sowlo_role.
   *
   * @return array
   *   A sowlo_role submission form.
   */
  public function addRole(RouteMatchInterface $route_match, UserInterface $user, RoleTypeInterface $sowlo_role_type) {

    $sowlo_role = $this->entityTypeManager()->getStorage('sowlo_role')->create([
      'uid' => $user->id(),
      'type' => $sowlo_role_type->id(),
    ]);

    return $this->entityFormBuilder()->getForm($sowlo_role, 'add', ['uid' => $user->id(), 'created' => REQUEST_TIME]);
  }

  /**
   * Provides the sowlo_role edit form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\sowlo_role\Entity\RoleInterface $sowlo_role
   *   The sowlo_role entity to edit.
   *
   * @return array
   *   The sowlo_role edit form.
   */
  public function editRole(RouteMatchInterface $route_match, UserInterface $user, RoleInterface $sowlo_role) {
    return $this->entityFormBuilder()->getForm($sowlo_role, 'edit');
  }

  /**
   * Provides sowlo_role delete form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\sowlo_role\Entity\RoleTypeInterface $sowlo_role_type
   *   The sowlo_role type entity for the sowlo_role.
   * @param int $id
   *   The id of the sowlo_role to delete.
   *
   * @return array
   *   Returns form array.
   */
  public function deleteRole(RouteMatchInterface $route_match, UserInterface $user, RoleTypeInterface $sowlo_role_type, $id) {
    return $this->entityFormBuilder()->getForm(Role::load($id), 'delete');
  }

  /**
   * The _title_callback for the add sowlo_role form route.
   *
   * @param \Drupal\sowlo_role\Entity\RoleTypeInterface $sowlo_role_type
   *   The current sowlo_role type.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(RoleTypeInterface $sowlo_role_type) {
    // @todo: edit sowlo_role uses this form too?
    return $this->t('Create @label', ['@label' => $sowlo_role_type->label()]);
  }

  /**
   * Provides sowlo_role create form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\sowlo_role\Entity\RoleTypeInterface $sowlo_role_type
   *   The sowlo_role type entity for the sowlo_role.
   *
   * @return array
   *    Returns form array.
   */
  public function userRoleForm(RouteMatchInterface $route_match, UserInterface $user, RoleTypeInterface $sowlo_role_type) {
    /** @var \Drupal\sowlo_role\Entity\RoleType $sowlo_role_type */

    /** @var \Drupal\sowlo_role\Entity\RoleInterface|bool $active_sowlo_role */
    $active_sowlo_role = $this->entityTypeManager()
      ->getStorage('sowlo_role')
      ->loadByUser($user, $sowlo_role_type->id());

    // If the sowlo_role type does not support multiple, only display an add form
    // if there are no entities, or an edit for the current.
    if (!$sowlo_role_type->getMultiple()) {

      // If there is an active sowlo_role, provide edit form.
      if ($active_sowlo_role) {
        return $this->editRole($route_match, $user, $active_sowlo_role);
      }

      // Else show the add form.
      return $this->addRole($route_match, $user, $sowlo_role_type);
    }
    // Display active, and link to create a sowlo_role.
    else {
      $build = [];

      // If there is no active sowlo_role, display add form.
      if (!$active_sowlo_role) {
        return $this->addRole($route_match, $user, $sowlo_role_type);
      }

      $build['add_sowlo_role'] = Link::createFromRoute(
        $this->t('Add new @type', ['@type' => $sowlo_role_type->label()]),
        'entity.sowlo_role.type.user_sowlo_role_form.add',
        ['user' => $this->currentUser()->id(), 'sowlo_role_type' => $sowlo_role_type->id()])
        ->toRenderable();

      // Render the active sowlo_roles.
      $build['active_sowlo_roles'] = [
        '#type' => 'view',
        '#name' => 'sowlo_roles',
        '#display_id' => 'sowlo_role_type_listing',
        '#arguments' => [$user->id(), $sowlo_role_type->id(), 1],
        '#embed' => TRUE,
        '#title' => $this->t('Active @type', ['@type' => $sowlo_role_type->label()]),
        '#pre_render' => [
          ['\Drupal\views\Element\View', 'preRenderViewElement'],
          'sowlo_role_views_add_title_pre_render',
        ],
      ];

      return $build;
    }
  }

  /**
   * Mark sowlo_role as default.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the currency listing.
   */
  public function setDefault(RouteMatchInterface $routeMatch) {
    /** @var \Drupal\sowlo_role\Entity\RoleInterface $sowlo_role */
    $sowlo_role = $routeMatch->getParameter('sowlo_role');
    $sowlo_role->setDefault(TRUE);
    $sowlo_role->save();

    drupal_set_message($this->t('The %label sowlo_role has been marked as default.', ['%label' => $sowlo_role->label()]));

    $url = $sowlo_role->toUrl('collection');
    return $this->redirect($url->getRouteName(), $url->getRouteParameters(), $url->getOptions());
  }

}
