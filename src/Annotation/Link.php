<?php

namespace App\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Link
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;
    /**
     * @Required
     *
     * @var string
     */
    public $route;
    public $params = array();
}