<?php
namespace WoohooLabs\Yin\JsonApi\Transformer;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\Included;

abstract class AbstractCompoundDocument extends AbstractDocument
{
    /**
     * @var mixed
     */
    protected $domainObject;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var \WoohooLabs\Yin\JsonApi\Schema\Included
     */
    protected $included;

    /**
     * Sets the value of the "data" and "included" properties based on the "resource" property.
     *
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     */
    abstract protected function setContent(RequestInterface $request);

    /**
     * Returns a response whose primary data is a relationship object with $relationshipName name.
     *
     * @param string $relationshipName
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @return array
     */
    abstract protected function getRelationshipContent($relationshipName, RequestInterface $request);

    /**
     * Returns a response with a status code of $responseCode, containing all the provided sections of the document,
     * assembled based on the $domainObject.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param mixed $domainObject
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param int $responseCode
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(ResponseInterface $response, $domainObject, RequestInterface $request, $responseCode)
    {
        $this->initializeDocument($domainObject);
        $content = $this->transformContent($request);

        return $this->doGetResponse($response, $responseCode, $content);
    }

    /**
     * Returns a response with a status code of $responseCode, only containing meta information (without the "data" and
     * the "included" sections) about the document, assembled based on the $domainObject.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param mixed $domainObject
     * @param int $responseCode
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getMetaResponse(ResponseInterface $response, $domainObject, $responseCode)
    {
        $this->initializeDocument($domainObject);
        $content = $this->transformBaseContent();

        return $this->doGetResponse($response, $responseCode, $content);
    }

    /**
     * Returns a response with a status code of $responseCode, containing the $relationshipName relationship object as
     * the primary data, assembled based on the $domainObject.
     *
     * @param string $relationshipName
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param mixed $domainObject
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @param int $responseCode
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getRelationshipResponse(
        $relationshipName,
        ResponseInterface $response,
        $domainObject,
        RequestInterface $request,
        $responseCode
    ) {
        $this->initializeDocument($domainObject);
        $content = $this->transformRelationshipContent($relationshipName, $request);

        return $this->doGetResponse($response, $responseCode, $content);
    }

    /**
     * @param mixed $domainObject
     */
    private function initializeDocument($domainObject)
    {
        $this->domainObject = $domainObject;
        $this->included = new Included();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $responseCode
     * @param array $content
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function doGetResponse(ResponseInterface $response, $responseCode, array $content)
    {
        $response = $response->withStatus($responseCode);
        $response = $response->withAddedHeader("Content-Type", $this->getContentType());
        $response->getBody()->rewind();
        $response->getBody()->write(json_encode($content));

        return $response;
    }

    /**
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @return array
     */
    protected function transformContent(RequestInterface $request)
    {
        $content = $this->transformBaseContent();

        // Data
        $this->setContent($request);
        $content["data"] = $this->data;

        // Included
        if ($this->included->isEmpty() === false) {
            $content["included"] = $this->included->transform();
        }

        return $content;
    }

    /**
     * @param string $relationshipName
     * @param \WoohooLabs\Yin\JsonApi\Request\RequestInterface $request
     * @return array
     */
    protected function transformRelationshipContent($relationshipName, RequestInterface $request)
    {
        $response = $this->getRelationshipContent($relationshipName, $request);

        // Included
        if ($this->included->isEmpty() === false) {
            $response["included"] = $this->included->transform();
        }

        return $response;
    }

    /**
     * @return \WoohooLabs\Yin\JsonApi\Schema\Included
     */
    public function getIncluded()
    {
        return $this->included;
    }
}
