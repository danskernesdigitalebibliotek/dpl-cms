<?php
/**
 * EventPATCHRequest
 *
 * PHP version 8.1.1
 *
 * @category Class
 * @package  DanskernesDigitaleBibliotek\CMS\Api\Model
 * @author   OpenAPI Generator team
 * @link     https://github.com/openapitools/openapi-generator
 */

/**
 * DPL CMS - REST API
 *
 * The REST API provide by the core REST module.
 *
 * The version of the OpenAPI document: Versioning not supported
 * 
 * Generated by: https://github.com/openapitools/openapi-generator.git
 *
 */

/**
 * NOTE: This class is auto generated by the openapi generator program.
 * https://github.com/openapitools/openapi-generator
 * Do not edit the class manually.
 */

namespace DanskernesDigitaleBibliotek\CMS\Api\Model;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Class representing the EventPATCHRequest model.
 *
 * @package DanskernesDigitaleBibliotek\CMS\Api\Model
 * @author  OpenAPI Generator team
 */

class EventPATCHRequest 
{
        /**
     * The state of the event.
     *
     * @var string|null
     * @SerializedName("state")
     * @Assert\Choice({ "TicketSaleNotOpen", "Active", "SoldOut", "Cancelled", "Occurred" })
     * @Assert\Type("string")
     * @Type("string")
     */
    protected ?string $state = null;

    /**
     * @var EventPATCHRequestExternalData|null
     * @SerializedName("external_data")
     * @Assert\Type("DanskernesDigitaleBibliotek\CMS\Api\Model\EventPATCHRequestExternalData")
     * @Type("DanskernesDigitaleBibliotek\CMS\Api\Model\EventPATCHRequestExternalData")
     */
    protected ?EventPATCHRequestExternalData $externalData = null;

    /**
     * Constructor
     * @param array|null $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        if (is_array($data)) {
            $this->state = array_key_exists('state', $data) ? $data['state'] : $this->state;
            $this->externalData = array_key_exists('externalData', $data) ? $data['externalData'] : $this->externalData;
        }
    }

    /**
     * Gets state.
     *
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }



    /**
     * Sets state.
     *
     * @param string|null $state  The state of the event.
     *
     * @return $this
     */
    public function setState(?string $state = null): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Gets externalData.
     *
     * @return EventPATCHRequestExternalData|null
     */
    public function getExternalData(): ?EventPATCHRequestExternalData
    {
        return $this->externalData;
    }



    /**
     * Sets externalData.
     *
     * @param EventPATCHRequestExternalData|null $externalData
     *
     * @return $this
     */
    public function setExternalData(?EventPATCHRequestExternalData $externalData = null): self
    {
        $this->externalData = $externalData;

        return $this;
    }
}

