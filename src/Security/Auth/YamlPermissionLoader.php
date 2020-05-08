<?php

namespace App\Security\Auth;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlPermissionLoader extends FileLoader
{

    public function load($resource, $type = null)
    {
        $configValues = Yaml::parse(file_get_contents($resource));

        return $configValues;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yaml' === pathinfo(
                $resource,
                PATHINFO_EXTENSION
            );
    }
}