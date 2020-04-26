<?php

namespace App\Model\Pagination;


use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PaginationCollection
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var Pagerfanta
     */
    private $pagerfanta;

    public function __construct(array $items, Pagerfanta $pagerfanta)
    {
        $this->items = $items;
        $this->pagerfanta = $pagerfanta;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return Pagerfanta
     */
    public function getPagerfanta(): Pagerfanta
    {
        return $this->pagerfanta;
    }
}