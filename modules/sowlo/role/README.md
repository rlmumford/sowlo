Role
-------

The *Role* module provides configurable user sowlo_roles.

## Requirements

* [Entity API](https://www.drupal.org/project/entity)

## Comparison to user account fields

Why use sowlo_roles instead of user account fields?

* With sowlo_role, user account settings and user sowlo_roles are conceptually different things, e.g. with the "Role" module enabled users get two separate menu links "My account" and "My sowlo_role".
* Role allows for creating multiple sowlo_role types, which may be assigned to roles via permissions (e.g. a general sowlo_role + a customer sowlo_role)
* Role supports private sowlo_role fields, which are only shown to the user owning the sowlo_role and to administrators.

## Features

* Multiple sowlo_role types may be created via the UI (e.g. a general sowlo_role + a customer sowlo_role), whereas the module provides separated permissions for those.
* Optionally, sowlo_role forms are shown during user account registration.
* Fields may be configured to be private - thus visible only to the sowlo_role owner and administrators.
* Role types are displayed on the user view page, and can be configured through "Manage Display" on account settings.