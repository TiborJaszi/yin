<?php
namespace WoohooLabs\Yin\Examples\User\JsonApi\Resource;

use WoohooLabs\Yin\JsonApi\Schema\Attributes;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer;

class ContactResourceTransformer extends AbstractResourceTransformer
{
    /**
     * Provides information about the "type" section of the current resource.
     *
     * The method returns the type of the current resource.
     *
     * @param array $contact
     * @return string
     */
    public function getType($contact)
    {
        return "contact";
    }

    /**
     * Provides information about the "id" section of the current resource.
     *
     * The method returns the ID of the current resource which should be a UUID.
     *
     * @param array $contact
     * @return string
     */
    public function getId($contact)
    {
        return $contact["id"];
    }

    /**
     * Provides information about the "meta" section of the current resource.
     *
     * The method returns an array of non-standard meta information about the resource. If
     * this array is empty, the section won't appear in the response.
     *
     * @param array $contact
     * @return array
     */
    public function getMeta($contact)
    {
        return [];
    }

    /**
     * Provides information about the "links" section of the current resource.
     *
     * The method returns a new Links schema object if you want to provide linkage
     * data about the resource or null if it should be omitted from the response.
     *
     * @param array $contact
     * @return \WoohooLabs\Yin\JsonApi\Schema\Links|null
     */
    public function getLinks($contact)
    {
        return new Links(
            [
                "self" => new Link("http://example.com/api/contacts/" . $this->getId($contact))
            ]
        );
    }

    /**
     * Provides information about the "attributes" section of the current resource.
     *
     * The method returns a new Attributes schema object if you want the section to
     * appear in the response of null if it should be omitted.
     *
     * @param array $contact
     * @return \WoohooLabs\Yin\JsonApi\Schema\Attributes|null
     */
    public function getAttributes($contact)
    {
        return new Attributes(
            [
                $contact["type"] => function(array $contact) { return $contact["value"]; },
            ]
        );
    }

    /**
     * Provides information about the "relationships" section of the current resource.
     *
     * The method returns a new Relationships schema object if you want the section to
     * appear in the response of null if it should be omitted.
     *
     * @param array $contact
     * @return \WoohooLabs\Yin\JsonApi\Schema\Relationships|null
     */
    public function getRelationships($contact)
    {
        return null;
    }
}
