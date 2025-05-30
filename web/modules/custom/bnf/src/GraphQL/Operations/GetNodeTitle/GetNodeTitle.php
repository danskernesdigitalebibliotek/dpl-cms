<?php declare(strict_types=1);

namespace Drupal\bnf\GraphQL\Operations\GetNodeTitle;

/**
 * @property string $__typename
 * @property \Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodeArticle|\Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodeGoArticle|\Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodeGoCategory|\Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodeGoPage|\Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodePage|null $node
 */
class GetNodeTitle extends \Spawnia\Sailor\ObjectLike
{
    /**
     * @param \Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodeArticle|\Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodeGoArticle|\Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodeGoCategory|\Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodeGoPage|\Drupal\bnf\GraphQL\Operations\GetNodeTitle\Node\NodePage|null $node
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
        /** @var array<string, \Spawnia\Sailor\Convert\TypeConverter>|null $converters */
        static $converters;

        return $converters ??= [
            '__typename' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\StringConverter),
            'node' => new \Spawnia\Sailor\Convert\NullConverter(new \Spawnia\Sailor\Convert\PolymorphicConverter([
            'NodeArticle' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNodeTitle\\Node\\NodeArticle',
            'NodeGoArticle' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNodeTitle\\Node\\NodeGoArticle',
            'NodeGoCategory' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNodeTitle\\Node\\NodeGoCategory',
            'NodeGoPage' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNodeTitle\\Node\\NodeGoPage',
            'NodePage' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNodeTitle\\Node\\NodePage',
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
