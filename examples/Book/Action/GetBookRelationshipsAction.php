<?php
namespace WoohooLabs\Yin\Examples\Book\Action;

use WoohooLabs\Yin\Examples\Book\JsonApi\Document\BookDocument;
use WoohooLabs\Yin\Examples\Book\JsonApi\Resource\AuthorResourceTransformer;
use WoohooLabs\Yin\Examples\Book\JsonApi\Resource\BookResourceTransformer;
use WoohooLabs\Yin\Examples\Book\JsonApi\Resource\PublisherResourceTransformer;
use WoohooLabs\Yin\Examples\Book\Repository\BookRepository;
use WoohooLabs\Yin\JsonApi\JsonApi;

class GetBookRelationshipsAction
{
    /**
     * @param \WoohooLabs\Yin\JsonApi\JsonApi $jsonApi
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(JsonApi $jsonApi)
    {
        $id = $jsonApi->getRequest()->getQueryParam("id");
        if ($id === null) {
            die("You must define the 'id' query parameter with a value of '1'!");
        }

        $relationshipName = $jsonApi->getRequest()->getQueryParam("relationship");
        if ($relationshipName === null) {
            die("You must define the 'relationship' query parameter with a value of 'authors' or 'publisher'!");
        }

        $book = BookRepository::getBook($id);

        $document = new BookDocument(
            new BookResourceTransformer(new AuthorResourceTransformer(), new PublisherResourceTransformer())
        );

        return $jsonApi->fetchRelationshipResponse($relationshipName)->ok($document, $book);
   }
}
