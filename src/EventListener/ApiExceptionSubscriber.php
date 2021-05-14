<?php

namespace App\EventListener;

use App\Api\ApiProblemException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();
        if (!$e instanceof ApiProblemException) {
            return;
        }
        $response = new JsonResponse([
                'message' => $e->getMessage(),
                'status_code' => $e->getStatusCode()
            ]
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);
    }
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }
}