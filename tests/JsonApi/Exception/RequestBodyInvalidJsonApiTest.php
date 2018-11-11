<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\Tests\JsonApi\Exception;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Exception\RequestBodyInvalidJsonApi;
use WoohooLabs\Yin\Tests\JsonApi\Double\StubRequest;

class RequestBodyInvalidJsonApiTest extends TestCase
{
    /**
     * @test
     */
    public function getErrorsWithTwoErrors()
    {
        $exception = $this->createException(
            "",
            [
                [
                    "message" => "abc",
                    "property" => "property1",
                ],
                [
                    "message" => "cde",
                    "property" => "",
                ],
            ]
        );

        $errors = $exception->getErrorDocument()->getErrors();

        $this->assertCount(2, $errors);
        $this->assertEquals("400", $errors[0]->getStatus());
        $this->assertEquals("Abc", $errors[0]->getDetail());
        $this->assertEquals("property1", $errors[0]->getSource()->getParameter());
        $this->assertEquals("400", $errors[1]->getStatus());
        $this->assertEquals("Cde", $errors[1]->getDetail());
        $this->assertNull($errors[1]->getSource());
    }

    /**
     * @test
     */
    public function getErrorDocumentWhenNotIncludeOriginal()
    {
        $exception = $this->createException("abc", [], false);

        $meta = $exception->getErrorDocument()->getMeta();

        $this->assertEmpty($meta);
    }

    /**
     * @test
     */
    public function getErrorDocumentWhenIncludeOriginal()
    {
        $exception = $this->createException("\"abc\"", [], true);

        $meta = $exception->getErrorDocument()->getMeta();

        $this->assertEquals(["original" => "abc"], $meta);
    }

    /**
     * @test
     */
    public function getValidationErrors()
    {
        $exception = $this->createException("", ["abc", "def"]);

        $validationErrors = $exception->getValidationErrors();

        $this->assertEquals(["abc", "def"], $validationErrors);
    }

    private function createException(string $body = "", array $validationErrors = [], bool $includeOriginal = false): RequestBodyInvalidJsonApi
    {
        $request = StubRequest::create();
        $request->getBody()->write($body);

        return new RequestBodyInvalidJsonApi($request, $validationErrors, $includeOriginal);
    }
}
