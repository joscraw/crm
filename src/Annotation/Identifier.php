<?php

namespace App\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Identifier
{
    private $identifiers;

    public function __construct(array $data)
    {
        if(isset($data['value'])) {
            $this->identifiers = \is_array($data['value']) ? $data['value'] : [$data['value']];
        }
    }

    /**
     * @return array
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }
}