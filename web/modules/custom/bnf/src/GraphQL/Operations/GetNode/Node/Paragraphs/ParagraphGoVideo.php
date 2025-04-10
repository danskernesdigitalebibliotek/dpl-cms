<?php declare(strict_types=1);

namespace Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs;

/**
 * @property string $id
 * @property \Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaAudio|\Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaDocument|\Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaImage|\Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaVideo|\Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaVideotool $embedVideo
 * @property string $title
 * @property string $__typename
 */
class ParagraphGoVideo extends \Spawnia\Sailor\ObjectLike
{
    /**
     * @param string $id
     * @param \Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaAudio|\Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaDocument|\Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaImage|\Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaVideo|\Drupal\bnf\GraphQL\Operations\GetNode\Node\Paragraphs\EmbedVideo\MediaVideotool $embedVideo
     * @param string $title
     */
    public static function make($id, $embedVideo, $title): self
    {
        $instance = new self;

        if ($id !== self::UNDEFINED) {
            $instance->id = $id;
        }
        if ($embedVideo !== self::UNDEFINED) {
            $instance->embedVideo = $embedVideo;
        }
        if ($title !== self::UNDEFINED) {
            $instance->title = $title;
        }
        $instance->__typename = 'ParagraphGoVideo';

        return $instance;
    }

    protected function converters(): array
    {
        static $converters;

        return $converters ??= [
            'id' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\IDConverter),
            'embedVideo' => new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\PolymorphicConverter([
            'MediaAudio' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\Paragraphs\\EmbedVideo\\MediaAudio',
            'MediaDocument' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\Paragraphs\\EmbedVideo\\MediaDocument',
            'MediaImage' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\Paragraphs\\EmbedVideo\\MediaImage',
            'MediaVideo' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\Paragraphs\\EmbedVideo\\MediaVideo',
            'MediaVideotool' => '\\Drupal\\bnf\\GraphQL\\Operations\\GetNode\\Node\\Paragraphs\\EmbedVideo\\MediaVideotool',
        ])),
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
