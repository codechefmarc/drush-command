<?php

namespace Drupal\custom_drush\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\path_alias\AliasManagerInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 */
final class CustomDrushCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a CustomDrushCommands object.
   */
  public function __construct(
    private readonly Token $token,
    private readonly AliasManagerInterface $pathAliasManager,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('token'),
      $container->get('path_alias.manager'),
    );
  }

  /**
   * Command description here.
   */
  #[CLI\Command(name: 'custom_drush:interal-path', aliases: ['intpath'])]
  #[CLI\Argument(name: 'pathAlias', description: 'The path alias, must begin with /')]
  #[CLI\Usage(name: 'custom_drush:interal-path /path-alias', description: 'Supply the path alias and the internal path will be retrieved.')]
  public function getInternalPath($pathAlias) {
    if (!str_starts_with($pathAlias, "/")) {
      $this->logger()->error(dt('The alias must start with a /'));
    }
    else {
      $path = $this->pathAliasManager->getPathByAlias($pathAlias);
      if ($path == $pathAlias) {
        $this->logger()->error(dt('There was no internal path found that uses that alias.'));
      }
      else {
        $this->output()->writeln($path);
      }

    }
    //$this->logger()->success(dt('Achievement unlocked.'));
  }

  /**
   * An example of the table output format.
   */
  #[CLI\Command(name: 'custom_drush:token', aliases: ['token'])]
  #[CLI\FieldLabels(labels: [
    'group' => 'Group',
    'token' => 'Token',
    'name' => 'Name'
  ])]
  #[CLI\DefaultTableFields(fields: ['group', 'token', 'name'])]
  #[CLI\FilterDefaultField(field: 'name')]
  public function token($options = ['format' => 'table']): RowsOfFields {
    $all = $this->token->getInfo();
    foreach ($all['tokens'] as $group => $tokens) {
      foreach ($tokens as $key => $token) {
        $rows[] = [
          'group' => $group,
          'token' => $key,
          'name' => $token['name'],
        ];
      }
    }
    return new RowsOfFields($rows);
  }

}
