<?php
namespace WoohooLabs\Yin\JsonApi\Schema\Pagination;

use WoohooLabs\Yin\JsonApi\Request\Pagination\OffsetPagination;
use WoohooLabs\Yin\JsonApi\Schema\Link;

trait OffsetBasedPaginationProviderTrait
{
    /**
     * @var int
     */
    abstract protected function getTotalItems();

    /**
     * @var int
     */
    abstract protected function getOffset();

    /**
     * @var int
     */
    abstract protected function getLimit();

    /**
     * @param string $url
     * @return \WoohooLabs\Yin\JsonApi\Schema\Link|null
     */
    public function getSelfLink($url)
    {
        if ($this->getOffset() < 0 || $this->getOffset() >= $this->getTotalItems()) {
            return null;
        }

        return $this->createPaginatedLink($url, $this->getOffset(), $this->getLimit());
    }

    /**
     * @param string $url
     * @return \WoohooLabs\Yin\JsonApi\Schema\Link|null
     */
    public function getFirstLink($url)
    {
        return $this->createPaginatedLink($url, 0, $this->getLimit());
    }

    /**
     * @param string $url
     * @return \WoohooLabs\Yin\JsonApi\Schema\Link|null
     */
    public function getLastLink($url)
    {
        if ($this->getOffset() + $this->getLimit() >= $this->getTotalItems()) {
            return null;
        }

        return $this->createPaginatedLink($url, $this->getOffset() + $this->getLimit() - 1, $this->getLimit());
    }

    /**
     * @param string $url
     * @return \WoohooLabs\Yin\JsonApi\Schema\Link|null
     */
    public function getPrevLink($url)
    {
        if ($this->getOffset() <= 0 || $this->getOffset() + $this->getLimit() >= $this->getTotalItems()) {
            return null;
        }

        if ($this->getOffset() - $this->getLimit() > 0) {
            $prevOffset = $this->getOffset() - $this->getLimit();
        } else {
            $prevOffset = 0;
        }

        return $this->createPaginatedLink($url, $prevOffset, $this->getLimit());
    }

    /**
     * @param string $url
     * @return \WoohooLabs\Yin\JsonApi\Schema\Link|null
     */
    public function getNextLink($url)
    {
        if ($this->getOffset() < 0 || $this->getOffset() + $this->getLimit() >= $this->getTotalItems()) {
            return null;
        }

        return $this->createPaginatedLink($url, $this->getOffset() + $this->getLimit(), $this->getLimit());
    }

    /**
     * @param string $url
     * @param int $page
     * @param int $size
     * @return \WoohooLabs\Yin\JsonApi\Schema\Link|null
     */
    protected function createPaginatedLink($url, $page, $size)
    {
        if ($this->getTotalItems() <= 0 || $this->getLimit() <= 0) {
            return null;
        }

        return new Link($this->appendQueryStringToUrl($url, OffsetPagination::getPaginationQueryString($page, $size)));
    }

    /**
     * @param string $url
     * @param string $queryString
     * @return string
     */
    protected function appendQueryStringToUrl($url, $queryString)
    {
        $separator = (parse_url($url, PHP_URL_QUERY) == NULL) ? '?' : '&';
        return $url . $separator . $queryString;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return int
     */
    protected function getLastItem($offset, $limit)
    {
        return $offset + $limit;
    }
}