<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MarketplaceQuery
 */
final class MarketplaceQuery
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $searchTerm;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var int
     */
    private $entitiesPerPage = 20;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var string
     */
    private $sort;

    /**
     * @var string
     */
    private $order;

    /**
     * @var int
     */
    private $servicepartnerId;

    /**
     * Query string builder
     *
     * @return string
     */
    public function __toString()
    {
        $query = '';
        if (!empty($this->categoryId)) {
            $query .= "&category={$this->categoryId}";
        }
        if (!empty($this->id)) {
            $query .= "&extension={$this->id}";
        }
        if (!empty($this->searchTerm)) {
            $query .= "&search=" . utf8_encode($this->searchTerm);
        }
        if (!empty($this->servicepartnerId)) {
            $query .= "&servicepartner={$this->servicepartnerId}";
        }
        if (!empty($this->offset)) {
            $query .= "&start={$this->offset}";
        }
        if (!empty($this->limit)) {
            $query .= "&limit={$this->limit}";
        }
        if (!empty($this->sort)) {
            $query .= "&sort={$this->sort}";
        }
        if (!empty($this->order)) {
            $query .= "&order={$this->order}";
        }

        return $query;
    }

    /**
     * Gets the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param int $id the id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * Gets the value of searchTerm.
     *
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * Sets the value of searchTerm.
     *
     * @param string $searchTerm the search term
     * @return $this
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }

    /**
     * Gets the value of categoryId.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Sets the value of categoryId.
     *
     * @param int $categoryId the category id
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = (int)$categoryId;

        return $this;
    }

    /**
     * Gets the value of page.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the value of page.
     *
     * @param int $page the page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = (int)$page;

        return $this;
    }

    /**
     * Gets the value of entitiesPerPage.
     *
     * @return int
     */
    public function getEntitiesPerPage()
    {
        return $this->entitiesPerPage;
    }

    /**
     * Sets the value of entitiesPerPage.
     *
     * @param int $entitiesPerPage the entities per page
     * @return $this
     */
    public function setEntitiesPerPage($entitiesPerPage)
    {
        $this->entitiesPerPage = (int)$entitiesPerPage;

        return $this;
    }

    /**
     * Gets the value of offset.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Sets the value of offset.
     *
     * @param int $offset the offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = (int)$offset;

        return $this;
    }

    /**
     * Gets the value of limit.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets the value of limit.
     *
     * @param int $limit the limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = (int)$limit;

        return $this;
    }

    /**
     * Gets the value of sort.
     *
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Sets the value of sort.
     *
     * @param string $sort the sort
     * @return $this
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Gets the value of order.
     *
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets the value of order.
     *
     * @param string $order the order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Gets the value of servicepartnerId.
     *
     * @return int
     */
    public function getServicepartnerId()
    {
        return $this->servicepartnerId;
    }

    /**
     * Sets the value of servicepartnerId.
     *
     * @param int $servicepartnerId the servicepartner id
     * @return $this
     */
    public function setServicepartnerId($servicepartnerId)
    {
        $this->servicepartnerId = (int)$servicepartnerId;

        return $this;
    }
}
