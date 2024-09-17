<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch\DTO;

/**
 * Node entity serializable payload object.
 */
class NodeEntityDto implements MobilesearchEntityInterface {

  /**
   * DTO constructor.
   */
  public function __construct(
    protected string $nid,
    protected string $agency,
    protected string $type,
    protected array $fields = [],
    protected array $taxonomy = [],
  ) {}

  /**
   * Gets node id.
   *
   * @return string
   *   Node id.
   */
  public function getNid(): string {
    return $this->nid;
  }

  /**
   * Sets node id.
   *
   * @param string $nid
   *   Node id.
   *
   * @return self
   *   DTO object.
   */
  public function setNid(string $nid): self {
    $this->nid = $nid;

    return $this;
  }

  /**
   * Gets node agency.
   *
   * @return string
   *   Sets node agency.
   */
  public function getAgency(): string {
    return $this->agency;
  }

  /**
   * Sets node agency.
   *
   * @param string $agency
   *   Node agency.
   *
   * @return self
   *   DTO object.
   */
  public function setAgency(string $agency): self {
    $this->agency = $agency;

    return $this;
  }

  /**
   *
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   *
   */
  public function setType(string $type): void {
    $this->type = $type;
  }

  /**
   * Gets node fields.
   *
   * @return array
   *   Node fields DTO array.
   */
  public function getFields(): array {
    return $this->fields;
  }

  /**
   * Sets node fields.
   *
   * @param array $fields
   *   Node fields DTO array.
   *
   * @return self
   *   DTO object.
   */
  public function setFields(array $fields): self {
    $this->fields = $fields;

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function jsonSerialize(): array {
    return [
      'nid' => $this->nid,
      'agency' => $this->agency,
      'type' => $this->type,
      'fields' => $this->fields,
      'taxonomy' => $this->taxonomy,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getId(): int {
    return $this->getNid();
  }

  /**
   * {@inheritDoc}
   */
  public function getRoute(): string {
    return 'content';
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityName(): string {
    return 'node';
  }

}
