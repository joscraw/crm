<?php

namespace App\Annotation;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class ApiRoute extends Route
{
    private $versions;

    private $scopes;

    public function __construct(array $data)
    {
        if(isset($data['versions'])) {
            $this->versions = \is_array($data['versions']) ? $data['versions'] : [$data['versions']];
            unset($data['versions']);
        }

        if(isset($data['scopes'])) {
            $this->scopes = \is_array($data['scopes']) ? $data['scopes'] : [$data['scopes']];
            unset($data['scopes']);
        }

        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function getVersions(): ?array
    {
        return $this->versions;
    }

    /**
     * @return array
     */
    public function getScopes(): ?array
    {
        return $this->scopes;
    }
}