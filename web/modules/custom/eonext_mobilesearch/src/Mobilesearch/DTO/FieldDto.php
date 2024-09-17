<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch\DTO;

/**
 * Single field DTO class.
 */
class FieldDto implements \JsonSerializable {

  /**
   * DTO constructor.
   */
  public function __construct(
    protected string $name,
    protected mixed $value,
    protected array $attr = [],
  ) {}

  /**
   * Gets field name.
   *
   * @return string
   *   Field name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Sets field name.
   *
   * @param string $name
   *   Field name.
   *
   * @return self
   *   DTO object.
   */
  public function setName(string $name): self {
    $this->name = $name;
    return $this;
  }

  /**
   * Gets field value.
   *
   * @return mixed
   *   Field value.
   */
  public function getValue(): mixed {
    return $this->value;
  }

  /**
   * Sets field value.
   *
   * @param mixed $value
   *   Field value (scalar).
   *
   * @return self
   *   DTO object.
   */
  public function setValue(mixed $value): self {
    $this->value = $value;
    return $this;
  }

  /**
   * Gets field attributes.
   *
   * @return array
   *   Field attributes (additional data).
   */
  public function getAttr(): array {
    return $this->attr;
  }

  /**
   * Sets field attributes.
   *
   * @param array $attr
   *   Field attributes (additional data)
   *
   * @return self
   *   DTO object.
   */
  public function setAttr(array $attr): self {
    $this->attr = $attr;

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function jsonSerialize(): array {
    return [
      'name' => $this->name,
      'value' => $this->value,
      'attr' => $this->attr,
    ];
  }

}
