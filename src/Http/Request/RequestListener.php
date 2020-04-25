<?php

namespace App\Http\Request;

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

        $url = $request->getPathInfo();
        $pattern = '/\/api\/(.+)\/private/';
        preg_match($pattern, $url, $matches);
        if(!empty($matches)) {
            $request->headers->set('X-Accept-Version', $matches[1]);
        }


    }
}