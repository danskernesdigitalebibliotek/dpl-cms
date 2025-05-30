<?php declare(strict_types=1);

namespace Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs;

/**
 * @property string $id
 * @property \Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\CqlSearch\CQLSearch $cqlSearch
 * @property int $sliderAmountOfMaterials
 * @property string $title
 * @property string $__typename
 */
class ParagraphGoMaterialSliderAutomatic extends \Spawnia\Sailor\ObjectLike
{
    /**
     * @param string $id
     * @param \Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\CqlSearch\CQLSearch $cqlSearch
     * @param int $sliderAmountOfMaterials
     * @param string $title
     */
    public static function make($id, $cqlSearch, $sliderAmountOfMaterials, $title): self
    {
        $instance = new self;

        if ($id !== self::UNDEFINED) {
            $instance->id = $id;
        }
        if ($cqlSearch !== self::UNDEFINED) {
            $instance->cqlSearch = $cqlSearch;
        }
        if ($sliderAmountOfMaterials !== self::UNDEFINED) {
            $instance->sliderAmountOfMaterials = $sliderAmountOfMaterials;
        }
        if ($title !== self::UNDEFINED) {
            $instance->title = $title;
        }
        $instance->__typename = 'ParagraphGoMaterialSliderAutomatic';

        return $instance;
    }

    protected function converters(): array
    {
        /** @var array<string, \Spawnia\Sailor\Convert\TypeConverter>|null $converters */
        static $converters;

        return $converters ??= [
            'id' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\IDConverter),
            'cqlSearch' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\CqlSearch\CQLSearch),
            'sliderAmountOfMaterials' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\IntConverter),
            'title' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\StringConverter),
            '__typename' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\StringConverter),
        ];
    }

    public static function endpoint(): string
    {
        return 'bnf';
    }

    public static function config(): string
    {
        return \Safe\realpath(__DIR__ . '/../../../../../../../../../../sailor.php');
    }
}
