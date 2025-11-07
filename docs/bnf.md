# Bibliotekernes Nationale Formidling

Bibliotekernes Nationale Formidling (henceforth "BNF") is a national
team handling sharing of content for libraries. The `bnf_server` and
`bnf_client` modules support their work.

## Server module

The server module is enabled on the main BNF site, which acts as a hub
for content sharing. The BNF team uses this site to create and edit
content provided for the libraries.

## Client module

The client module is enabled on library sites and handles pushing and
fetching content to/from the BNF site.

## Overview

BNF support sharing of content in to ways:

1. Manually, by browsing
   [Delingstjenesten](https://delingstjenesten.dk/) and manually
   picking content to import to the local library site.
2. Through subscriptions where new content from
   [Delingstjenesten](https://delingstjenesten.dk/) is automatically
   imported to the local library. These can be created by visiting a
   term listing page on
   [Delingstjenesten](https://delingstjenesten.dk/) and opting to
   subscribe to the term.

A node that has been imported is automatically updated with updates
from [Delingstjenesten](https://delingstjenesten.dk/), but the local
editor can opt to turn off updates for individual nodes. This is
required if they want to change the content, in order to avoid having
overwritten by an upstream update.

## The nitty gritty details

The `bnf` module adds three fields nodes, one containing the
synchronization state (imported/exported/none), last source changed
timestamp, the name of the source and which subscription(s) (if any)
it was imported through..

When synchronizing a node, the nodes UUID is preserved, so content
thus shared has the same UUID on all sites regardless of what node ID
they happen to get. This means we don't have to maintain a NID mapping
in order to keep track of which node corresponds to which on
delingstjenesten.dk or the client sites.

Subscriptions is an entity defined by the `bnf_client` module. A
subscription has an UUID, a label (for display purposes), the UUID of
a taxonomy term on delingstjenesten.dk, optional categories and
tags to associate created content with, and a timestamp for the newest
imported node.

### The UI

The actual UI parts of BNF is minimal. Client side there's a button to
"log in" on delingstjenesten.dk, which sends the user to the BNF site
and adds buttons to nodes and taxonomy terms to import nodes and
create subscriptions.

In reality the library doesn't log into delingstjenesten.dk, the "Log
in" link simply redirects to delingstjenesten.dk with some parameters
that tells the BNF site where they're coming from, which is then
associated with that anonymous user session.

The import and subscribe buttons are equally simple, they simply
redirect back to the client site with an UUID, and the client module
then initiates synchronization/subscription creation.

On the client site there's an administration page for subscriptions.

On delingstjenesten.dk there's no real UI for the editorial team. They
simply use the same editorial tools for content as if the site was a
regular library.

### Synchronization

The synchronization process is handled by cron and queues on the
client side, and is done over GraphQL. There's two queues: One that
re-synchronizes existing nodes when they're updated, and one that asks
for new content on subscriptions and creates new nodes.

The node synchronization code uses the node query endpoints provided
by the `graphql_compose` module to fetch the node data. We use the
Sailor GraphQL client generator tool to generate a client with
response classes in `Drupal\bnf\GraphQL\Operations`.

This provides us with a typed response to queries. We then pass these
response objects to `BnfMapperManager`, which tries to find a mapper
plugin that handles the given response class. These mapper classes
might in turn call the manager to map other mappers recursively. For
instance, the `NodeArticleMapper` knows to pass the objects of the
`field_paragraphs` field to the manager to get the individual
paragraphs mapped.

This process maps the responses back to node object that's then saved
locally.

## Programmatically Adding BNF Subscriptions

In addition to creating subscriptions manually via the BNF user
interface, subscriptions can also be added programmatically through
custom update hooks or module code.  
This is useful when you need to ensure that certain libraries or
environments automatically subscribe to predefined BNF content
streams (for example, _"Om eReolen"_).

### Example: Adding a Subscription in an Update Hook

You can define an update hook in your custom module (for example,
`dpl_update.install`) to add subscriptions automatically during a
deployment or site update:

```php
/**
 * Add the "Om eReolen" subscription to BNF with automatic tagging.
 */
function dpl_update_update_10062(): string {
  return _dpl_update_add_bnf_subscription(
    '6f204837-ed6f-4683-b8c9-4200005ac1ae',
    'Om eReolen',
    'Om eReolen'
  );
}
```

This example adds a BNF subscription for the UUID
`6f204837-ed6f-4683-b8c9-4200005ac1ae`,  
with both the label and automatic tag set to _“Om eReolen”_.

### Helper Function: `_dpl_update_add_bnf_subscription()`

The helper function encapsulates the logic for creating a BNF
subscription and optionally linking it to a taxonomy tag:

```php
/**
 * Helper function to add a BNF subscription.
 *
 * @param string $uuid
 *   The UUID of the subscription to add.
 * @param string $label
 *   The label for the subscription.
 * @param string|null $tag_name
 *   Optional tag name to create and associate with the subscription.
 *   If provided, a taxonomy term will be created in the 'tags' vocabulary
 *   and the subscription will automatically tag all imported content
 *   with this term.
 *
 * @return string
 *   Feedback message.
 */
function _dpl_update_add_bnf_subscription(
  string $uuid,
  string $label,
  ?string $tag_name = NULL
): string {
  $feedback = [];

  if (!\Drupal::moduleHandler()->moduleExists('bnf_client')) {
    return 'The bnf_client module is not enabled. Subscription could not be created.';
  }

$entity_type_manager = DrupalTyped::service(
  EntityTypeManagerInterface::class,
  'entity_type.manager'
);
  $subscription_storage = $entity_type_manager->getStorage('bnf_subscription');

  /** @var \Drupal\bnf_client\Entity\Subscription[] $existing */
  $existing = $subscription_storage->loadByProperties([
    'subscription_uuid' => $uuid,
  ]);

  if ($existing) {
    return "The subscription '$label' ($uuid) already exists. Skipping creation.";
  }

  // Create the subscription.
  $subscription_data = [
    'subscription_uuid' => $uuid,
    'label' => $label,
  ];

  // Create and associate taxonomy term if tag name is provided.
  if (!empty($tag_name)) {
    $term_storage = $entity_type_manager->getStorage('taxonomy_term');

    // Check if tag already exists.
    $existing_terms = $term_storage->loadByProperties([
      'name' => $tag_name,
      'vid' => 'tags',
    ]);

    if ($existing_terms) {
      $tag_term = reset($existing_terms);
      $feedback[] = "Found existing tag '$tag_name' (ID: {$tag_term->id()}).";
    }
    else {
      // Create new taxonomy term.
      $tag_term = Term::create([
        'name' => $tag_name,
        'vid' => 'tags',
      ]);
      $tag_term->save();
      $feedback[] = "Created new tag '$tag_name' (ID: {$tag_term->id()}).";
    }

    // Add the tag to the subscription data.
    $subscription_data['tags'] = [['target_id' => $tag_term->id()]];
  }

  $subscription = $subscription_storage->create($subscription_data);
  $subscription->save();

  $feedback[] = "Successfully created subscription for '$label' ($uuid).";

  if (!empty($tag_name)) {
$feedback[] = 'Subscription configured to automatically tag imported content ' .
  "with '$tag_name'.";
  }

  return implode("\n", $feedback);
}
```

### Notes

- The function checks if the **`bnf_client`** module is enabled before proceeding.
- It automatically avoids duplicate subscriptions based on the UUID.
- If a tag name is provided:
  - The function ensures a taxonomy term exists in the `'tags'` vocabulary.
  - The subscription is configured to automatically tag imported content.
- The function returns a feedback string summarizing what actions were taken.

### Example Output

When running the update hook, typical feedback might look like:

```text
Created new tag 'Om eReolen' (ID: 1234).
Successfully created subscription for 'Om eReolen' (6f204837-ed6f-4683-b8c9-4200005ac1ae).
Subscription configured to automatically tag imported content with 'Om eReolen'.
```

**File:** `web/modules/custom/dpl_update/dpl_update.install`  
**Purpose:** Automatically configure BNF subscriptions for client sites with
            tagging support.
