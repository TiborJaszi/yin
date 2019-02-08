<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\Tests\JsonApi\Schema\Document;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequest;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\Data\DataInterface;
use WoohooLabs\Yin\JsonApi\Schema\Data\SingleResourceData;
use WoohooLabs\Yin\JsonApi\Schema\Document\ErrorDocumentInterface;
use WoohooLabs\Yin\JsonApi\Schema\Document\ResourceDocumentInterface;
use WoohooLabs\Yin\JsonApi\Schema\Error\Error;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;
use WoohooLabs\Yin\JsonApi\Schema\Link\DocumentLinks;
use WoohooLabs\Yin\JsonApi\Serializer\JsonDeserializer;
use WoohooLabs\Yin\JsonApi\Transformer\DocumentTransformer;
use WoohooLabs\Yin\JsonApi\Transformer\ErrorDocumentTransformation;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceDocumentTransformation;
use WoohooLabs\Yin\Tests\JsonApi\Double\StubErrorDocument;
use WoohooLabs\Yin\Tests\JsonApi\Double\StubJsonApiRequest;
use WoohooLabs\Yin\Tests\JsonApi\Double\StubResourceDocument;
use Zend\Diactoros\ServerRequest;

class DocumentTransformerTest extends TestCase
{
    /**
     * @test
     */
    public function transformMetaDocumentWithoutJsonApiObject()
    {
        $document = $this->createDocument(null);

        $transformedDocument = $this->toMetaDocument($document, []);

        $this->assertEquals(
            [],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformMetaDocumentWithJsonApiObject()
    {
        $document = $this->createDocument(new JsonApiObject("1.0"));

        $transformedDocument = $this->toMetaDocument($document, []);

        $this->assertEquals(
            [
                "jsonapi" => [
                    "version" => "1.0",
                ],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformMetaDocumentWithMeta()
    {
        $document = $this->createDocument(null, ["abc" => "def"]);

        $transformedDocument = $this->toMetaDocument($document, []);

        $this->assertEquals(
            [
                "meta" => [
                    "abc" => "def",
                ],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformMetaDocumentWithEmptyLinks()
    {
        $document = $this->createDocument(null, [], new DocumentLinks());

        $transformedDocument = $this->toMetaDocument($document, []);

        $this->assertEquals(
            [
                "links" => [],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformResourceDocumentWithEmptyData()
    {
        $document = $this->createDocument(null, [], null, new SingleResourceData());

        $transformedDocument = $this->toResourceDocument($document, []);

        $this->assertEquals(
            [
                "data" => null,
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformResourceDocumentWithEmptyIncluded()
    {
        $document = $this->createDocument(null, [], null, new SingleResourceData());

        $transformedDocument = $this->toResourceDocument($document, [], new StubJsonApiRequest(["include" => "animal"]));

        $this->assertEquals(
            [
                "data" => null,
                "included" => [],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformRelationshipDocumentWithEmptyIncluded()
    {
        $document = $this->createDocument(
            null,
            [],
            null,
            new SingleResourceData(),
            [
                "data" => [],
            ]
        );

        $transformedDocument = $this->toRelationshipDocument($document, [], new StubJsonApiRequest(["include" => "animal"]));

        $this->assertEquals(
            [
                "data" => [],
                "included" => [],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformRelationshipDocumentWithIncluded()
    {
        $document = $this->createDocument(
            null,
            [],
            null,
            (new SingleResourceData())
                ->setIncludedResources(
                    [
                        [
                            "type" => "user",
                            "id" => "2",
                        ],
                        [
                            "type" => "user",
                            "id" => "3",
                        ],
                    ]
                )
        );

        $transformedDocument = $this->toRelationshipDocument($document, []);

        $this->assertEquals(
            [
                "included" => [
                    [
                        "type" => "user",
                        "id" => "2",
                    ],
                    [
                        "type" => "user",
                        "id" => "3",
                    ],
                ],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformRelationshipDocumentByIncludedQueryParam()
    {
        $document = $this->createDocument();

        $transformedDocument = $this->toRelationshipDocument($document, [], new StubJsonApiRequest(["include" => "animal"]));

        $this->assertEquals(
            [
                "included" => [],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformErrorDocumentWithoutJsonApiObject()
    {
        $document = $this->createErrorDocument(null);

        $transformedDocument = $this->toErrorDocument($document);

        $this->assertEquals(
            [],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformErrorDocumentWithJsonApiObject()
    {
        $document = $this->createErrorDocument(new JsonApiObject(""));

        $transformedDocument = $this->toErrorDocument($document);

        $this->assertEquals(
            [
                "jsonapi" => [],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformErrorDocumentWithMeta()
    {
        $document = $this->createErrorDocument(null, ["abc" => "def"]);

        $transformedDocument = $this->toErrorDocument($document);

        $this->assertEquals(
            [
                "meta" => [
                    "abc" => "def",
                ],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformErrorDocumentWithLinks()
    {
        $document = $this->createErrorDocument(null, [], new DocumentLinks());

        $transformedDocument = $this->toErrorDocument($document);

        $this->assertEquals(
            [
                "links" => [],
            ],
            $transformedDocument
        );
    }

    /**
     * @test
     */
    public function transformErrorDocumentWithErrors()
    {
        $document = $this->createErrorDocument(null, [], null, [new Error(), new Error()]);

        $transformedDocument = $this->toErrorDocument($document);

        $this->assertEquals(
            [
                "errors" => [
                    [],
                    [],
                ],
            ],
            $transformedDocument
        );
    }

    /**
     * @param mixed $object
     */
    private function toMetaDocument(
        ResourceDocumentInterface $document,
        $object,
        ?JsonApiRequestInterface $request = null,
        string $requestedRelationshipName = ""
    ): array {
        $transformation = new ResourceDocumentTransformation(
            $document,
            $object,
            $request ? $request : new JsonApiRequest(
                new ServerRequest(),
                new DefaultExceptionFactory(),
                new JsonDeserializer()
            ),
            "",
            $requestedRelationshipName,
            [],
            new DefaultExceptionFactory()
        );

        $transformer = new DocumentTransformer();

        return $transformer->transformMetaDocument($transformation)->result;
    }

    /**
     * @param mixed $object
     */
    private function toResourceDocument(
        ResourceDocumentInterface $document,
        $object,
        ?JsonApiRequestInterface $request = null,
        string $requestedRelationshipName = ""
    ): array {
        $transformation = new ResourceDocumentTransformation(
            $document,
            $object,
            $request ? $request : new JsonApiRequest(
                new ServerRequest(),
                new DefaultExceptionFactory(),
                new JsonDeserializer()
            ),
            "",
            $requestedRelationshipName,
            [],
            new DefaultExceptionFactory()
        );

        $transformer = new DocumentTransformer();

        return $transformer->transformResourceDocument($transformation)->result;
    }

    /**
     * @param mixed $object
     */
    private function toRelationshipDocument(
        ResourceDocumentInterface $document,
        $object,
        ?JsonApiRequestInterface $request = null,
        string $requestedRelationshipName = ""
    ): array {
        $transformation = new ResourceDocumentTransformation(
            $document,
            $object,
            $request ? $request : new JsonApiRequest(
                new ServerRequest(),
                new DefaultExceptionFactory(),
                new JsonDeserializer()
            ),
            "",
            $requestedRelationshipName,
            [],
            new DefaultExceptionFactory()
        );

        $transformer = new DocumentTransformer();

        return $transformer->transformRelationshipDocument($transformation)->result;
    }

    private function toErrorDocument(ErrorDocumentInterface $document, ?JsonApiRequestInterface $request = null): array
    {
        $transformation = new ErrorDocumentTransformation(
            $document,
            $request ? $request : new JsonApiRequest(
                new ServerRequest(),
                new DefaultExceptionFactory(),
                new JsonDeserializer()
            ),
            [],
            new DefaultExceptionFactory()
        );

        $transformer = new DocumentTransformer();

        return $transformer->transformErrorDocument($transformation)->result;
    }

    private function createDocument(
        ?JsonApiObject $jsonApi = null,
        array $meta = [],
        ?DocumentLinks $links = null,
        ?DataInterface $data = null,
        array $relationshipResponseContent = []
    ): ResourceDocumentInterface {
        return new StubResourceDocument(
            $jsonApi,
            $meta,
            $links,
            $data,
            $relationshipResponseContent
        );
    }

    /**
     * @param Error[] $errors
     */
    private function createErrorDocument(
        ?JsonApiObject $jsonApi = null,
        array $meta = [],
        ?DocumentLinks $links = null,
        array $errors = []
    ): ErrorDocumentInterface {
        return new StubErrorDocument($jsonApi, $meta, $links, $errors);
    }
}