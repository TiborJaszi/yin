<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\JsonApi\Exception;

use WoohooLabs\Yin\JsonApi\Schema\Error\Error;

class ResourceIdentifierIdMissing extends AbstractJsonApiException
{
    /**
     * @var array
     */
    private $resourceIdentifier;

    public function __construct(array $resourceIdentifier)
    {
        parent::__construct("An ID for the resource identifier must be included!", 400);
        $this->resourceIdentifier = $resourceIdentifier;
    }

    protected function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("400")
                ->setCode("RESOURCE_IDENTIFIER_ID_MISSING")
                ->setTitle("An ID for the resource identifier is missing")
                ->setDetail("An ID for the resource identifier must be included!"),
        ];
    }

    public function getResourceIdentifier(): array
    {
        return $this->resourceIdentifier;
    }
}
