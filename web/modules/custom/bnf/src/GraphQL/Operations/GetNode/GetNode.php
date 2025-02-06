<?php declare(strict_types=1);

namespace Drupal\bnf\GraphQL\Operations\GetNode;

/**
 * @property string $__typename
 * @property \Drupal\bnf\GraphQL\Operations\GetNode\Node\NodeArticle|\Drupal\bnf\GraphQL\Operations\GetNode\Node\NodeGoArticle|\Drupal\bnf\GraphQL\Operations\GetNode\Node\NodeGoCategory|\Drupal\bnf\GraphQL\Operations\GetNode\Node\NodeGoPage|null $node
 */
class GetNode extends \Spawnia\Sailor\ObjectLike
{
    /**
     * @param \Drupal\bnf\GraphQL\Operations\GetNode\Node\NodeArticle|\Drupal\bnf\GraphQL\Operations\GetNode\Node\NodeGoArticle|\Drupal\bnf\GraphQL\Operations\GetNode\Node\NodeGoCategory|\Drupal\bnf\GraphQL\Operations\GetNode\Node\NodeGoPage|null $node
     */
    public static function make(
        $node = 'Special default value that allows Sailor to differentiate between explicitly passing null and not passing a value at all.',
    ): self {
        $instance = new self;

        $instance->__typename = 'Query';
        if ($node !== self::UNDEFINED) {
            $instance->node = $node;
        }

        return $instance;
    }

    protected function converters(): array
    {
        static $converters;

        return $converters ??= [
            '__typename' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\StringConverter),
            'node' => new \Spawnia\Sailor\Convert\NullConverter(new \Spawnia\Sailor\Convert\PolymorphicConverter([
            'NodeArticle' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\NodeArticle',
            'NodeGoArticle' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\NodeGoArticle',
            'NodeGoCategory' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\NodeGoCategory',
            'NodeGoPage' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\NodeGoPage',
        ])),
        ];
    }

    public static function endpoint(): string
    {
        return 'bnf';
    }

    public static function config(): string
    {
        return \Safe\realpath(__DIR__ . '/../../../../../../../../sailor.php');
    }
}
