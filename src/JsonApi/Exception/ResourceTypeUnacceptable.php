<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\JsonApi\Exception;

use WoohooLabs\Yin\JsonApi\Schema\Error\Error;
use WoohooLabs\Yin\JsonApi\Schema\Error\ErrorSource;

class ResourceTypeUnacceptable extends AbstractJsonApiException
{
    /**
     * @var mixed
     */
    protected $currentType;

    /**
     * @var array
     */
    protected $acceptedTypes;

    /**
     * @param mixed $currentType
     */
    public function __construct($currentType, array $acceptedTypes)
    {
        parent::__construct("Resource type '$currentType' is not a string or can't be accepted by the Hydrator!", 409);
        $this->currentType = $currentType;
        $this->acceptedTypes = $acceptedTypes;
    }

    public function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("409")
                ->setCode("RESOURCE_TYPE_UNACCEPTABLE")
                ->setTitle("Resource type is unacceptable")
                ->setDetail("Resource type '$this->currentType' is unacceptable!")
                ->setSource(ErrorSource::fromPointer("/data/type")),
        ];
    }

    /**
     * @return mixed
     */
    public function getCurrentType()
    {
        return $this->currentType;
    }

    public function getAcceptedTypes(): array
    {
        return $this->acceptedTypes;
    }
}
