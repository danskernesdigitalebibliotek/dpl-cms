<?php

namespace Drupal\dpl_library_agency\Plugin\GraphQL\SchemaExtension;

use Drupal\dpl_library_agency\Branch\Branch;
use Drupal\dpl_library_agency\Plugin\Field\FieldFormatter\AddressDawaFormatter;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * Branches extension.
 *
 * @SchemaExtension(
 *   id = "dpl_library_agency_branches",
 *   name = "Library agency branches extension",
 *   description = "Exposes library branches via GraphQL",
 *   schema = "graphql_compose"
 * )
 */
class BranchesExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Query', 'getBranches',
      $builder->produce('get_branches_producer')
        ->map('isilId', $builder->fromArgument('isilId'))
        ->map('whitelistType', $builder->fromArgument('whitelistType'))
        ->map('cmsConfigured', $builder->fromArgument('cmsConfigured'))
    );

    $registry->addFieldResolver('Branch', 'isilId',
      $builder->callback(fn(Branch $branch) => $branch->id)
    );

    $registry->addFieldResolver('Branch', 'title',
      $builder->callback(fn(Branch $branch) => $branch->title)
    );

    $registry->addFieldResolver('Branch', 'address',
      $builder->callback(function (Branch $branch): ?array {
        $addressData = $branch->getAddressData();
        if ($addressData === NULL) {
          return NULL;
        }

        return AddressDawaFormatter::buildOutput($addressData);
      })
    );

    $registry->addFieldResolver('BranchAddress', 'street',
      $builder->callback(fn(array $address) => $address['address'])
    );

    $registry->addFieldResolver('BranchAddress', 'postalCode',
      $builder->callback(fn(array $address) => $address['postal_code'])
    );

    $registry->addFieldResolver('BranchAddress', 'city',
      $builder->callback(fn(array $address) => $address['city'])
    );

    $registry->addFieldResolver('BranchAddress', 'country',
      $builder->callback(fn(array $address) => $address['country'])
    );
  }

}
