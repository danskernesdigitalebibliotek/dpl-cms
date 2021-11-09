# Architecture Decision Record: Caching of DDB React and other js resources.

## Context
The general caching strategy is defined in another document and this focused on describing the caching strategy of DDB react and other js resources.

We need to have a caching strategy that makes sure that:
* The js files defined as Drupal libraries (which ddb react is) and pages that make use of them are being cached.
* The same cache is being flushed upon deploy because that is the moment where new versions of DDB React can be introduced.

## Decision

We have created a purger in the Drupal Varnish/Purge setup that is able to purge everything.
The purger is being used in the deploy routine by the command: `drush cache:rebuild-external -y`

## Note
By studing the vcl of Lagoon we found that the PURGE request actually is being translated into a BAN on req.url.