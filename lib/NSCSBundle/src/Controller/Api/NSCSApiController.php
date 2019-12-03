<?php

namespace NSCSBundle\Controller\Api;

use App\Entity\Portal;
use Doctrine\ORM\EntityManagerInterface;
use NSCSBundle\Repository\RecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class NSCSApiController extends  AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * NSCSApiController constructor.
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param RecordRepository $recordRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        RecordRepository $recordRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->recordRepository = $recordRepository;
    }

    public function authorizationCheck(Request $request) {

        $email = $request->request->get('emailAddress');
        $invitationCode = $request->request->get('invitationCode');
        $result = $this->recordRepository->getContactByEmailAndInvitationCode($email, $invitationCode);
        if(empty($result['results'])) {
            return $this->json([
               'success' => false,
                'message' => 'user not found in the system'
            ]);
        }
        return $this->json([
           'success' => true,
            'message' => 'user successfully found'
        ]);
    }
}