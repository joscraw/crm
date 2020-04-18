<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class PrivateApiRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request)
    {
        $url = $request->getPathInfo();
        $isMatch = strpos($url, "/api/private") === 0;
        return $isMatch;
    }
}