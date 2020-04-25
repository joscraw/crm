<?php

namespace App\EventListener;

use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // only run this logic if the client expects a json response
        $request = $event->getRequest();
        if (0 !== strpos($request->headers->get('Accept'), 'application/json')) {
            return;
        }

        $e = $event->getException();

        if(method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        if($request->query->has('verbosity')) {
            $message = $e->getMessage();
        } else {
            $message = null;
        }

        // We want to make sure all our exceptions for the api come back uniformly
        if (!$e instanceof ApiException) {
            throw new ApiException(new ApiErrorResponse(
                $message,
                null,
                [],
                $statusCode
            ));
            return;
        }

        $event->setResponse($e->getApiErrorResponse());
    }
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }
}