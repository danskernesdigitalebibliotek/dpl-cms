<?php

namespace Drupal\dpl_library_agency\Plugin\GraphQL\SchemaExtension;

use Drupal\dpl_library_agency\Branch\Branch;
use Drupal\dpl_library_agency\BranchSettings;
use Drupal\dpl_library_agency\Plugin\Field\FieldFormatter\AddressDawaFormatter;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The branch settings service.
   */
  protected BranchSettings $branchSettings;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->branchSettings = $container->get(BranchSettings::class);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Query', 'getBranches',
      $builder->produce('get_branches_producer')
        ->map('isilId', $builder->fromArgument('isilId'))
        ->map('whitelistTypes', $builder->fromArgument('whitelistTypes'))
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

    $registry->addFieldResolver('Branch', 'whitelists',
      $builder->callback(function (Branch $branch): array {
        return [
          'search' => !in_array($branch->id, $this->branchSettings->getExcludedSearchBranches(), TRUE),
          'availability' => !in_array($branch->id, $this->branchSettings->getExcludedAvailabilityBranches(), TRUE),
          'reservations' => !in_array($branch->id, $this->branchSettings->getExcludedReservationBranches(), TRUE),
        ];
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

    $registry->addFieldResolver('BranchWhitelists', 'search',
      $builder->callback(fn(array $whitelists) => $whitelists['search'])
    );

    $registry->addFieldResolver('BranchWhitelists', 'availability',
      $builder->callback(fn(array $whitelists) => $whitelists['availability'])
    );

    $registry->addFieldResolver('BranchWhitelists', 'reservations',
      $builder->callback(fn(array $whitelists) => $whitelists['reservations'])
    );
  }

}
