<?php

namespace App\Model\Pagination;

use Pagerfanta\Adapter\AdapterInterface;

class StaticCollectionAdapter implements AdapterInterface
{
    private $items = [];

    /**
     * StaticCollectionAdapter constructor.
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults()
    {
        return count($this->items);
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        return array_slice($this->items, $offset, $length);
    }
}