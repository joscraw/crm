<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IsArrayExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('isArray', [$this, 'isArray']),
        ];
    }

    public function isArray($value)
    {
        return is_array($value);
    }
}