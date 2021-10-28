# Architecture Decision Record: Configuration Management

## Context

Configuration management for DPL CMS is a complex issue. The complexity stems
from the following factors:

There are multiple types of DPL CMS sites all using the same code base:

1. *Developer* (In Danish: Programmør) sites where the library is entirely free to
work with the codebase for DPL CMS as they please for their site
2. *Webmaster* sites where the library can install and
manage additional modules for their DPL CMS site
3. *Editor* (In Danish: Redaktør) sites where the library can configure their site
based on predefined configuration options provided by DPL CMS
4. *Core* sites which are default versions of DPL CMS used for development and
testing purposes

All these site types must support the following properties:

1. It must be possible for system administrators to deploy new versions of
DPL CMS which may include changes to the site configuration
2. It must be possible for libraries to configure their site based on the
options provided by their type site. This configuration must not be overridden
by new versions of DPL CMS.

## Decision

We use the [Configuration Split module](https://www.drupal.org/project/config_split) to manage configuration.

Drupal supports configuration stored in two locations:

1. *Filesystem* - optimal for configuration which is tracked in version control
and deployed as a part of changes to the source code for the configuration
2. *Database* - optimal for configuration which can be managed runtime
   using the administration interface

Configuration Split allows us to match these configuration storage options with
different types of configuration.

A *Core setting* with filesystem based storage manages all configuration which is
managed by DPL CMS for all but developer sites. This will be imported on
deployment to support central development of the system.

A *Local setting* with databased based storage manages library managed
configuration provided by existing and new modules. This is not overridden on
deployment.

Since we cannot know what configuration keys are provided by new modules
uploaded on webmaster sites and we do not want to override this in on deployment
we use a wildcard to include all other configuration in this setting.

## Alternatives considered

### Deconfig + Partial Imports

[The Deconfig module](https://www.drupal.org/project/deconfig) allows developers
to mark configuration entries as exempt from import/export. This would allow us
to exempt configuration which can be managed by the library.

This does not handle configuration coming from new modules uploaded on webmaster
sites. Since we cannot know which configuration entities such modules will
provide and Deconfig has no concept of wildcards we cannot exempt the
configuration from these modules. Their configuration will be removed again at
deployment.

We could use partial imports through `drush config-import --partial` to not remove
configuration which is not present in the configuration filesystem.

We prefer Config Split as it provides a single solution to handle the entire
problem space.


### Config Ignore

[The Config Ignore module](https://www.drupal.org/project/config_ignore) allows
developers to ignore certain configuration entries based on their path selected
through a set of advanced configuration rules.

We could support both configuration properties by ignoring all configuration by
default using a general wildcard and then subsequently excepting all core
configuration from the ignore.

We prefer Config Split as it provides a user interface with an overview of all
available configuration on the site. Once you have added a general wildcard to
your rules in Config Ignore you have no way to provide an overview of the
available configuration.


## Consequences

- Core developers will have to explicitly select new configuration to include
in the Core setting during the development process. One can not simply run
`drush config-export` and expect all changes to be exported.
- It is possible to add additional configuration split settings e.g. to support
development or to handle additional configuration for programmer sites.
