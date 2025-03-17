<?php declare(strict_types=1);

namespace Drupal\bnf\GraphQL\Operations;

/**
 * @extends \Spawnia\Sailor\Operation<\Drupal\bnf\GraphQL\Operations\GetNode\GetNodeResult>
 */
class GetNode extends \Spawnia\Sailor\Operation
{
    /**
     * @param int|string $id
     */
    public static function execute($id): GetNode\GetNodeResult
    {
        return self::executeOperation(
            $id,
        );
    }

    protected static function converters(): array
    {
        static $converters;

        return $converters ??= [
            ['id', new \Spawnia\Sailor\Convert\NonNullConverter(new \Spawnia\Sailor\Convert\IDConverter)],
        ];
    }

    public static function document(): string
    {
        return /* @lang GraphQL */ 'query GetNode($id: ID!) {
          __typename
          node(id: $id) {
            __typename
            ... on NodeInterface {
              id
              title
            }
            ... on NodeArticle {
              paragraphs {
                __typename
                ... on ParagraphTextBody {
                  id
                  body {
                    __typename
                    value
                    format
                  }
                }
                ... on ParagraphLinks {
                  id
                  link {
                    __typename
                    title
                    url
                    internal
                  }
                }
                ... on ParagraphBanner {
                  id
                  bannerImage {
                    __typename
                    ... on MediaImage {
                      id
                      name
                      mediaImage {
                        __typename
                        title
                        url
                        alt
                      }
                    }
                  }
                  underlinedTitle {
                    __typename
                    value
                    format
                  }
                  bannerDescription
                  bannerLink {
                    __typename
                    title
                    url
                  }
                }
                ... on ParagraphAccordion {
                  id
                  accordionDescription {
                    __typename
                    value
                    format
                  }
                  accordionTitle {
                    __typename
                    value
                    format
                  }
                }
                ... on ParagraphFiles {
                  id
                  files {
                    __typename
                    ... on MediaDocument {
                      id
                      name
                      mediaFile {
                        __typename
                        url
                        description
                        name
                      }
                    }
                  }
                }
                ... on ParagraphGoLink {
                  id
                  targetBlank
                  singleLink: link {
                    __typename
                    title
                    url
                    internal
                  }
                }
                ... on ParagraphHero {
                  id
                  heroCategories {
                    __typename
                    ... on TermCategories {
                      id
                      name
                    }
                  }
                  heroContentType
                  heroDescription {
                    __typename
                    value
                    format
                  }
                  heroDate {
                    __typename
                    timestamp
                    timezone
                    time
                  }
                  heroImage {
                    __typename
                    ... on MediaImage {
                      id
                      name
                      mediaImage {
                        __typename
                        title
                        url
                        alt
                      }
                    }
                  }
                  heroLink {
                    __typename
                    title
                    url
                    internal
                  }
                  heroTitle
                }
                ... on ParagraphMaterialGridAutomatic {
                  id
                  materialGridDescription
                  materialGridTitle
                  amountOfMaterials
                  cqlSearch {
                    __typename
                    value
                  }
                }
                ... on ParagraphMaterialGridLinkAutomatic {
                  id
                  materialGridDescription
                  materialGridLink
                  materialGridTitle
                  amountOfMaterials
                }
                ... on ParagraphMaterialGridManual {
                  id
                  materialGridDescription
                  materialGridTitle
                  materialGridWorkIds {
                    __typename
                    material_type
                    work_id
                  }
                }
                ... on ParagraphMedias {
                  id
                  medias {
                    __typename
                    ... on MediaImage {
                      id
                      name
                      mediaImage {
                        __typename
                        title
                        url
                        alt
                      }
                    }
                  }
                }
                ... on ParagraphRecommendation {
                  id
                  imagePositionRight
                  recommendationDescription
                  recommendationTitle {
                    __typename
                    value
                    format
                  }
                  recommendationWorkId {
                    __typename
                    material_type
                    work_id
                  }
                }
                ... on ParagraphSimpleLinks {
                  id
                  link {
                    __typename
                    title
                    url
                    internal
                  }
                }
                ... on ParagraphVideo {
                  id
                  embedVideoRequired: embedVideo {
                    __typename
                    ... on MediaVideo {
                      id
                      name
                      mediaOembedVideo
                    }
                    ... on MediaVideotool {
                      id
                      name
                      mediaVideotool
                    }
                  }
                }
              }
            }
            ... on NodeGoArticle {
              id
              paragraphs {
                __typename
                ... on ParagraphGoLinkbox {
                  id
                  goColor
                  goDescription
                  goImage {
                    __typename
                    ... on MediaImage {
                      name
                      mediaImage {
                        __typename
                        title
                        url
                        alt
                      }
                      id
                    }
                  }
                  goLinkParagraph {
                    __typename
                    ... on ParagraphGoLink {
                      id
                      link {
                        __typename
                        title
                        url
                        internal
                      }
                      ariaLabel
                      targetBlank
                    }
                  }
                  titleRequired: title
                }
                ... on ParagraphGoMaterialSliderAutomatic {
                  id
                  cqlSearch {
                    __typename
                    value
                  }
                  sliderAmountOfMaterials
                  title
                }
                ... on ParagraphGoMaterialSliderManual {
                  id
                  materialSliderWorkIds {
                    __typename
                    material_type
                    work_id
                  }
                  title
                }
                ... on ParagraphGoVideo {
                  id
                  embedVideo {
                    __typename
                    ... on MediaVideo {
                      id
                      name
                      mediaOembedVideo
                    }
                    ... on MediaVideotool {
                      id
                      name
                      mediaVideotool
                    }
                  }
                  titleRequired: title
                }
                ... on ParagraphGoVideoBundleAutomatic {
                  id
                  cqlSearch {
                    __typename
                    value
                  }
                  embedVideo {
                    __typename
                    ... on MediaVideo {
                      id
                      name
                      mediaOembedVideo
                    }
                    ... on MediaVideotool {
                      id
                      name
                      mediaVideotool
                    }
                  }
                  goVideoTitle
                  videoAmountOfMaterials
                }
                ... on ParagraphGoVideoBundleManual {
                  id
                  embedVideo {
                    __typename
                    ... on MediaVideo {
                      id
                      name
                      mediaOembedVideo
                    }
                    ... on MediaVideotool {
                      id
                      name
                      mediaVideotool
                    }
                  }
                  goVideoTitle
                  videoBundleWorkIds {
                    __typename
                    material_type
                    work_id
                  }
                }
              }
            }
          }
        }';
    }

    public static function endpoint(): string
    {
        return 'bnf';
    }

    public static function config(): string
    {
        return \Safe\realpath(__DIR__ . '/../../../../../../../sailor.php');
    }
}
