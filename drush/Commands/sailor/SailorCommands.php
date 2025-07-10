<?php

declare(strict_types=1);

namespace Drush\Commands\sailor;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drush\Attributes as Cli;
use Drush\Boot\DrupalBootLevels;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Spawnia\Sailor\Console\IntrospectCommand;
use Spawnia\Sailor\Console\CodegenCommand;
use Symfony\Component\Console\Input\ArrayInput;
use function Safe\chdir;

/**
 * Drush commands for running Sailor.
 */
class SailorCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(protected ModuleHandlerInterface $moduleHandler) {
  }

  /**
   * Validate that the module exists.
   */
  #[Cli\Hook(type: HookManager::ARGUMENT_VALIDATOR)]
  public function validate(CommandData $commandData): void {
    // `getModule()` will throw on unknown modules.
    $this->moduleHandler->getModule($commandData->input()->getArgument('module'));
  }

  /**
   * Run sailor inspect.
   */
  #[Cli\Command(name: 'sailor:introspect')]
  #[Cli\Argument(name: 'module', description: 'Name of module to run introspection on.')]
  #[Cli\Bootstrap(level: DrupalBootLevels::FULL)]
  public function introspect(string $module): void {
    $introspect = new IntrospectCommand();
    $input = new ArrayInput([]);

    $this->chDirToModule($module);
    $introspect->run($input, $this->output());
  }

  /**
   * Run sailor codegen.
   */
  #[Cli\Command(name: 'sailor:codegen')]
  #[Cli\Argument(name: 'module', description: 'Name of module to run codegen on.')]
  public function codegen(string $module): void {
    $introspect = new CodegenCommand();
    $input = new ArrayInput([]);

    $this->chDirToModule($module);
    $introspect->run($input, $this->output());
  }

  /**
   * Change to the dir of the module.
   */
  protected function chDirToModule(string $module): void {
    chdir($this->moduleHandler->getModule($module)->getPath());
  }

}
