<?php

namespace App\Messenger;

use App\Message\WorkflowActionMessage;
use App\Repository\WorkflowActionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Serializer\SerializerInterface;

class AuditMiddleware implements MiddlewareInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var WorkflowActionRepository
     */
    private $workflowActionRepository;

    /**
     * AuditMiddleware constructor.
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param WorkflowActionRepository $workflowActionRepository
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        WorkflowActionRepository $workflowActionRepository
    ) {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->workflowActionRepository = $workflowActionRepository;
    }


    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last(UniqueIdStamp::class)) {
            $envelope = $envelope->with(new UniqueIdStamp());
        }

        /** @var UniqueIdStamp $stamp */
        $stamp = $envelope->last(UniqueIdStamp::class);
        dump($stamp->getUniqueId());
        return $stack->next()->handle($envelope, $stack);
    }
}