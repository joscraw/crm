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

    public function emailCheck(Request $request) {
        $email = $request->request->get('emailAddress');
        $result = $this->recordRepository->getContactByEmail($email);
        if(empty($result['results'])) {
            return $this->json([
                'success' => false,
                'message' => 'email not found in the system'
            ]);
        }
        return $this->json([
            'success' => true,
            'message' => 'email successfully found'
        ]);
    }

    public function invitationCheck(Request $request) {
        $invitationCode = $request->request->get('invitationCode');
        $result = $this->recordRepository->getContactByInvitationCode($invitationCode);
        if(empty($result['results'])) {
            return $this->json([
                'success' => false,
                'message' => 'invitation code not found in the system'
            ]);
        }
        return $this->json([
            'success' => true,
            'message' => 'invitation successfully found'
        ]);
    }

    public function getCengageScholarships(Request $request) {
        try {
            $limit = $request->query->get('limit', 10);
            $offset = $request->query->get('offset', 0);
            $search = $request->query->get('search', '');
            $tag = $request->query->get('tag', '');
            $results = $this->recordRepository->getCengageScholarships($limit, $offset, $search, $tag);
        } catch (\Exception $exception) {
            if(empty($results['results'])) {
                return $this->json([
                    'success' => false
                ]);
            }
        }
        foreach($results["results"] as &$result) {
            $result['properties'] = json_decode($result['properties'], true);
        }
        return $this->json([
            'success' => true,
            'data' => $results['results'],
            'total_count' => $this->recordRepository->getCengageScholarshipCount($search, $tag),
        ]);
    }

    public function getCengageScholarshipsCount(Request $request) {
        try {
            $search = $request->query->get('search', '');
            $tag = $request->query->get('tag', '');
            $results = $this->recordRepository->getCengageScholarshipCount($search, $tag);
        } catch (\Exception $exception) {
            if(empty($results['results'])) {
                return $this->json([
                    'success' => false
                ]);
            }
        }
        return $this->json([
            'success' => true,
            'total_count' => $results['results'][0]['count']
        ]);
    }

    public function getCengageScholarshipTags(Request $request) {
        try {
            $results = $this->recordRepository->getCengageScholarshipTags();
        } catch (\Exception $exception) {
            if(empty($results['results'])) {
                return $this->json([
                    'success' => false
                ]);
            }
        }
        return $this->json([
            'success' => true,
            'data' => $results['results']
        ]);
    }

}