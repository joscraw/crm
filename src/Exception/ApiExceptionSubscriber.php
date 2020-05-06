<?php

namespace App\Exception;

use App\Http\ApiErrorResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $env;

    public function __construct($env)
    {
        $this->env = $env;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // only run this logic if the client expects a json response
        $request = $event->getRequest();
        if (0 !== strpos($request->headers->get('Accept'), 'application/json')) {
            return;
        }

        $e = $event->getException();

        // We don't want to override default http status codes
        // for system defined exceptions that provide their own.
        // If they do not have their own, fall back to a plain old 500
        if(method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        } else if ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getCode();
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        // Exception messages shouldn't be exposed in production as this is a
        // security hole. Only show exception messages with verbosity when
        // the verbosity query param exists and the app is not running in production
        if($request->query->has('verbosity') && strtolower($this->env) !== 'prod') {
            $message = $e->getMessage();
        } else {
            $message = null;
        }

        // We want to make sure all our exceptions for the api come back uniformly
        // so always pipe every exception through our ApiException
        if (!$e instanceof ApiException) {
            $apiErrorResponse = new ApiErrorResponse(
                $message,
                null,
                [],
                $statusCode
            );
        } else {
            $apiErrorResponse = $e->getApiErrorResponse();
        }

        $event->setResponse($apiErrorResponse);
    }
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }
}