<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class MarketingRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request)
    {
        $url = $request->getPathInfo();
        $pattern = '/\/api\/.+\/marketing/';
        return preg_match($pattern, $url, $matches);
    }
}