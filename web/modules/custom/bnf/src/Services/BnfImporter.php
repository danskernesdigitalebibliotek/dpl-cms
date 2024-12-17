<?php

namespace Drupal\bnf\Services;

use Drupal\bnf\Exception\AlreadyExistsException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\paragraphs\ParagraphInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use function Safe\json_decode;
use function Safe\parse_url;
use function Safe\preg_replace;

/**
 * Service related to importing content from an external source.
 *
 * This is both relevant for the client and the server:
 * The client can request BNF to import a certain content (see BnfExporter),
 * and the client can also choose to import a content from BNF.
 */
class BnfImporter {

  const UUID_FIELD = 'bnf_uuid';

  const ALLOWED_PARAGRAPHS = [
    'text_body',
  ];

  /**
   * The BNF UUID of the content that we import.
   */
  protected string $uuid;

  /**
   * Constructor.
   */
  public function __construct(
    protected ClientInterface $httpClient,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected TranslationInterface $translation,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Building the query we use to get data from source.
   */
  protected function getQuery(string $queryName): string {
    // Example of GraphQL query: "nodeArticle".
    // For now, we only support the title of the nodes.
    return <<<GRAPHQL
    query {
      $queryName(id: "$this->uuid") {
        title
        paragraphs {
          ... on ParagraphTextBody {
            __typename
            body {
              format,
              value
            }
          }
        }
      }
    }
    GRAPHQL;

  }

  /**
   * Parses paragraphs from GraphQL node data into Drupal-compatible structures.
   *
   * @param mixed[] $nodeData
   *   The GraphQL node data containing paragraphs.
   *
   * @return mixed[]
   *   Array of paragraph values, that we can use to create paragraph entities.
   */
  protected function parseParagraphs(array $nodeData) {
    $parsedParagraphs = [];

    // Ensure paragraphs exist in the GraphQL response.
    if (empty($nodeData['paragraphs'])) {
      return $parsedParagraphs;
    }

    foreach ($nodeData['paragraphs'] as $paragraphData) {
      $type = $paragraphData['__typename'] ?? '';

      // Convert typename to Drupal paragraph bundle name.
      $bundleName = $this->graphqlTypeToBundle($type);

      if (!in_array($bundleName, self::ALLOWED_PARAGRAPHS)) {
        continue;
      }

      $paragraph = ['type' => $bundleName];

      // Map fields dynamically.
      foreach ($paragraphData as $key => $value) {
        if ($key === '__typename') {
          continue;
        }

        // Assume Drupal uses field names like "field_{key}".
        $drupalFieldName = 'field_' . $key;
        $paragraph[$drupalFieldName] = $value;
      }

      $parsedParagraphs[] = $paragraph;
    }

    return $parsedParagraphs;
  }

  /**
   * Creating the paragraphs, that we will add to the nodes.
   *
   * @param mixed[] $nodeData
   *   The GraphQL node data containing paragraphs.
   *
   * @return \Drupal\paragraphs\ParagraphInterface[]
   *   The paragraph entities.
   */
  protected function getParagraphs(array $nodeData): array {
    $parsedParagraphs = $this->parseParagraphs($nodeData);
    $storage = $this->entityTypeManager->getStorage('paragraph');
    $paragraphs = [];
    foreach ($parsedParagraphs as $paragraphData) {
      $paragraph = $storage->create($paragraphData);

      if ($paragraph instanceof ParagraphInterface) {
        $paragraph->save();
        $paragraphs[] = $paragraph;
      }
    }

    return $paragraphs;
  }

  /**
   * Converts a GraphQL typename to a Drupal paragraph bundle name.
   *
   * @param string $typeName
   *   The GraphQL typename (e.g., ParagraphTextBody).
   *
   * @return string
   *   The Drupal paragraph bundle name (e.g., text_body).
   */
  protected function graphqlTypeToBundle(string $typeName): string {
    // Removing 'Paragraph' prefix.
    $typeName = preg_replace('/^Paragraph/', '', $typeName);

    // Converting CamelCase to snake_case.
    $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=[0-9])/';
    $typeName = preg_replace($pattern, '_', $typeName);

    return strtolower($typeName);
  }

  /**
   * Importing a node from a GraphQL source endpoint.
   */
  public function importNode(string $uuid, string $endpointUrl, string $nodeType = 'article'): void {
    $this->uuid = $uuid;
    $queryName = 'node' . ucfirst($nodeType);

    $nodeStorage = $this->entityTypeManager->getStorage('node');

    $existingNodes =
      $nodeStorage->loadByProperties([self::UUID_FIELD => $uuid]);

    if (!empty($existingNodes)) {
      $this->logger->error(
        'Cannot import @type @uuid from @url - Node already exists.',
        ['@type' => $nodeType, '@uuid' => $uuid, '@url' => $endpointUrl]
      );

      throw new AlreadyExistsException('Cannot import node - already exists.');
    }

    if (!filter_var($endpointUrl, FILTER_VALIDATE_URL)) {
      throw new \InvalidArgumentException('The provided callback URL is not valid.');
    }

    $parsedUrl = parse_url($endpointUrl);
    $scheme = $parsedUrl['scheme'] ?? NULL;

    if ($scheme !== 'https') {
      throw new \InvalidArgumentException('The provided callback URL must use HTTPS.');
    }

    $query = $this->getQuery($queryName);

    $response = $this->httpClient->request('post', $endpointUrl, [
      'headers' => [
        'Content-Type' => 'application/json',
      ],
      'auth' => [getenv('GRAPHQL_USER_NAME'), getenv('GRAPHQL_USER_PASSWORD')],
      'json' => [
        'query' => $query,
      ],
      // Make sure that the server is HTTPS.
      'verify' => TRUE,
    ]);

    $data = json_decode($response->getBody()->getContents(), TRUE);
    $nodeData = $data['data'][$queryName] ?? NULL;

    if (empty($nodeData)) {
      $this->logger->error('Could not find any node data in GraphQL response.');

      throw new \Exception('Could not retrieve content values.');
    }

    try {
      $nodeData['type'] = $nodeType;
      $nodeData[self::UUID_FIELD] = $uuid;
      $nodeData['field_paragraphs'] = $this->getParagraphs($nodeData);

      $node = $nodeStorage->create($nodeData);
      $node->save();
    }
    catch (\Exception $e) {
      $this->logger->error(
        'Failed to create node data. @message',
        ['@message' => $e->getMessage()]
      );

      throw new \Exception('Could not save content.');
    }

    $this->logger->info('Created new @type node with BNF ID @uuid', [
      '@uuid' => $uuid,
      '@type' => $nodeType,
    ]);

  }

}
