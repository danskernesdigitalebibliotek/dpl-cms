<?php

namespace Drupal\eonext_mobilesearch\Mobilesearch\DTO;

/**
 * MobileSearch request DTO.
 */
class RequestDto implements \JsonSerializable {

  /**
   * DTO constructor.
   *
   * @param array $credentials
   *   Authorization information.
   * @param \JsonSerializable|null $body
   *   Request payload body.
   */
  public function __construct(
    protected array $credentials = [],
    protected ?\JsonSerializable $body = NULL,
  ) {}

  /**
   * Sets authorization credentials.
   *
   * @param string $agencyId
   *   Agency ID.
   * @param string $key
   *   Authorization key.
   *
   * @return \Drupal\eonext_mobilesearch\Mobilesearch\DTO\RequestDto
   *   Request payload object.
   */
  public function setCredentials(string $agencyId, string $key): self {
    $this->credentials['agencyId'] = $agencyId;
    $this->credentials['key'] = $key;

    return $this;
  }

  /**
   * Gets authorization credentials.
   *
   * @return array
   *   Credentials information.
   */
  public function getCredentials(): array {
    return $this->credentials;
  }

  /**
   * Sets request payload body.
   *
   * @param \JsonSerializable $body
   *   Data to send.
   *
   * @return \Drupal\eonext_mobilesearch\Mobilesearch\DTO\RequestDto
   *   Request payload object.
   */
  public function setBody(\JsonSerializable $body): self {
    $this->body = $body;

    return $this;
  }

  /**
   * Gets request payload body.
   *
   * @return \JsonSerializable|null
   *   Body object.
   */
  public function getBody(): ?\JsonSerializable {
    return $this->body;
  }

  /**
   * {@inheritDoc}
   */
  public function jsonSerialize(): mixed {
    return [
      'credentials' => $this->credentials,
      'body' => $this->body,
    ];
  }

}
