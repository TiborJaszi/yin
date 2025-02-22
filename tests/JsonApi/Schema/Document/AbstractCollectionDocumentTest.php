<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\Tests\JsonApi\Schema\Document;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Schema\Resource\ResourceInterface;
use WoohooLabs\Yin\Tests\JsonApi\Double\StubCollectionDocument;
use WoohooLabs\Yin\Tests\JsonApi\Double\StubResource;

class AbstractCollectionDocumentTest extends TestCase
{
    /**
     * @test
     */
    public function getResource(): void
    {
        $resource = new StubResource();

        $collectionDocument = $this->createCollectionDocument($resource);

        $this->assertEquals($resource, $collectionDocument->getResource());
    }

    /**
     * @test
     */
    public function hasItemsTrue(): void
    {
        $resource = new StubResource();

        $collectionDocument = $this->createCollectionDocument($resource, [[], []]);

        $this->assertTrue($collectionDocument->getHasItems());
    }

    /**
     * @test
     */
    public function hasItemsFalse(): void
    {
        $resource = new StubResource();

        $collectionDocument = $this->createCollectionDocument($resource, []);

        $this->assertFalse($collectionDocument->getHasItems());
    }

    /**
     * @test
     */
    public function getItemsFalse(): void
    {
        $resource = new StubResource();

        $collectionDocument = $this->createCollectionDocument($resource, []);

        $this->assertFalse($collectionDocument->getHasItems());
    }

    private function createCollectionDocument(?ResourceInterface $resource = null, iterable $object = []): StubCollectionDocument
    {
        return new StubCollectionDocument(null, [], null, $resource, $object);
    }
}
