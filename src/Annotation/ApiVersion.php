<?php

namespace App\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class ApiVersion
{
    private $versions;

    public function __construct(array $data)
    {
        if(isset($data['value'])) {
            $this->versions = \is_array($data['value']) ? $data['value'] : [$data['value']];
        }
    }

    /**
     * @return array
     */
    public function getVersions(): ?array
    {
        return $this->versions;
    }
}