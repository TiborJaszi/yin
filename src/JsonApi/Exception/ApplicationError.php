<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\JsonApi\Exception;

use WoohooLabs\Yin\JsonApi\Schema\Error\Error;

class ApplicationError extends AbstractJsonApiException
{
    public function __construct()
    {
        parent::__construct("Application exception is thrown!", 500);
    }

    protected function getErrors(): array
    {
        return [
            Error::create()
                ->setStatus("500")
                ->setCode("APPLICATION_ERROR")
                ->setTitle("Application error")
                ->setDetail("An application error has occurred!"),
        ];
    }
}
