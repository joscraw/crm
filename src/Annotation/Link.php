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
    public $rel;
    /**
     * @Required
     *
     * @var string
     */
    public $href;

    public function __construct(array $data)
    {
        if(isset($data['rel'])) {
            $this->rel = $data['rel'];
        }

        if(isset($data['href'])) {
            $this->href= $data['href'];
        }
    }
}