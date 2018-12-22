<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Content
 * @package App\Model
 */
class Content
{
    /**
     * @var ArrayCollection|Property[]
     */
    protected $properties;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }
}