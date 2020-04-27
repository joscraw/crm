<?php

namespace App\Http\Request;

use App\Annotation\ApiVersion;
use App\Http\Api;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        // This is used to map the JSON to the request object
        $request = $event->getRequest();
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }

        if (0 === strpos($request->headers->get('Accept'), 'application/json')) {
            $request->setRequestFormat('json');
        } else {
            $request->setRequestFormat('html');
        }

        // Let's pull the version and the scope from the api
        $url = $request->getPathInfo();
        $urlParts = array_values(array_filter(explode("/", $url)));
        if(!empty($urlParts[1]) && in_array($urlParts[1], Api::$versions)) {
            $request->headers->set('X-Accept-Version', $urlParts[1]);
        }

        if(!empty($urlParts[2]) && in_array($urlParts[2], Api::$scopes)) {
            $request->headers->set('X-Accept-Scope', $urlParts[2]);
        }
    }
}