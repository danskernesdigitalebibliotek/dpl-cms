<?php
/**
 * AuthenticatedPatronV8
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  DanskernesDigitaleBibliotek\FBS
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * FBS Adapter
 *
 * No description provided (generated by Openapi Generator https://github.com/openapitools/openapi-generator)
 *
 * The version of the OpenAPI document: 1.0
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 7.1.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace DanskernesDigitaleBibliotek\FBS\Model;

use \ArrayAccess;
use \DanskernesDigitaleBibliotek\FBS\ObjectSerializer;

/**
 * AuthenticatedPatronV8 Class Doc Comment
 *
 * @category Class
 * @package  DanskernesDigitaleBibliotek\FBS
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class AuthenticatedPatronV8 implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'AuthenticatedPatronV8';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'authenticate_status' => 'string',
        'patron' => '\DanskernesDigitaleBibliotek\FBS\Model\PatronV7'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'authenticate_status' => null,
        'patron' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'authenticate_status' => false,
		'patron' => false
    ];

    /**
      * If a nullable field gets set to null, insert it here
      *
      * @var boolean[]
      */
    protected array $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'authenticate_status' => 'authenticateStatus',
        'patron' => 'patron'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'authenticate_status' => 'setAuthenticateStatus',
        'patron' => 'setPatron'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'authenticate_status' => 'getAuthenticateStatus',
        'patron' => 'getPatron'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }

    public const AUTHENTICATE_STATUS_VALID = 'VALID';
    public const AUTHENTICATE_STATUS_INVALID = 'INVALID';
    public const AUTHENTICATE_STATUS_LOANER_LOCKED_OUT = 'LOANER_LOCKED_OUT';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getAuthenticateStatusAllowableValues()
    {
        return [
            self::AUTHENTICATE_STATUS_VALID,
            self::AUTHENTICATE_STATUS_INVALID,
            self::AUTHENTICATE_STATUS_LOANER_LOCKED_OUT,
        ];
    }

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->setIfExists('authenticate_status', $data ?? [], null);
        $this->setIfExists('patron', $data ?? [], null);
    }

    /**
    * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
    * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
    * $this->openAPINullablesSetToNull array
    *
    * @param string $variableName
    * @param array  $fields
    * @param mixed  $defaultValue
    */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['authenticate_status'] === null) {
            $invalidProperties[] = "'authenticate_status' can't be null";
        }
        $allowedValues = $this->getAuthenticateStatusAllowableValues();
        if (!is_null($this->container['authenticate_status']) && !in_array($this->container['authenticate_status'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'authenticate_status', must be one of '%s'",
                $this->container['authenticate_status'],
                implode("', '", $allowedValues)
            );
        }

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets authenticate_status
     *
     * @return string
     */
    public function getAuthenticateStatus()
    {
        return $this->container['authenticate_status'];
    }

    /**
     * Sets authenticate_status
     *
     * @param string $authenticate_status AuthenticateStatus:  <ul>      <li>- 'VALID': successfully authenticated</li>      <li>- 'INVALID': either the user is not known in the system, or an invalid combination of authentication parameters has been used.</li>      <li>- 'LOANER_LOCKED_OUT': user has been blocked temporary because of too many failed login attempts</li>  </ul>
     *
     * @return self
     */
    public function setAuthenticateStatus($authenticate_status)
    {
        if (is_null($authenticate_status)) {
            throw new \InvalidArgumentException('non-nullable authenticate_status cannot be null');
        }
        $allowedValues = $this->getAuthenticateStatusAllowableValues();
        if (!in_array($authenticate_status, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'authenticate_status', must be one of '%s'",
                    $authenticate_status,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['authenticate_status'] = $authenticate_status;

        return $this;
    }

    /**
     * Gets patron
     *
     * @return \DanskernesDigitaleBibliotek\FBS\Model\PatronV7|null
     */
    public function getPatron()
    {
        return $this->container['patron'];
    }

    /**
     * Sets patron
     *
     * @param \DanskernesDigitaleBibliotek\FBS\Model\PatronV7|null $patron patron
     *
     * @return self
     */
    public function setPatron($patron)
    {
        if (is_null($patron)) {
            throw new \InvalidArgumentException('non-nullable patron cannot be null');
        }
        $this->container['patron'] = $patron;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


