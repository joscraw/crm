<?php

namespace NSCSBundle\Controller\Api;

use App\Entity\Portal;
use App\Entity\Record;
use Doctrine\ORM\EntityManagerInterface;
use NSCSBundle\Repository\CustomObjectRepository;
use NSCSBundle\Repository\PortalRepository;
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
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PortalRepository
     */
    private $portalRepository;

    /**
     * NSCSApiController constructor.
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param RecordRepository $recordRepository
     * @param CustomObjectRepository $customObjectRepository
     * @param PortalRepository $portalRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        RecordRepository $recordRepository,
        CustomObjectRepository $customObjectRepository,
        PortalRepository $portalRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->recordRepository = $recordRepository;
        $this->customObjectRepository = $customObjectRepository;
        $this->portalRepository = $portalRepository;
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

    public function getChapters(Request $request) {
        try {
            $limit = $request->query->get('limit', 10);
            $offset = $request->query->get('offset', 0);
            $search = $request->query->get('search', '');
            $results = $this->recordRepository->getChapters($limit, $offset, $search);
        } catch (\Exception $exception) {
            if(empty($results['results'])) {
                return $this->json([
                    'success' => false
                ]);
            }
        }
        foreach($results["results"] as &$result) {
            $result['account_properties'] = json_decode($result['account_properties'], true);
            $result['event_properties'] = json_decode($result['event_properties'], true);
            $result['event_registration_properties'] = json_decode($result['event_registration_properties'], true);
            $result['contact_properties'] = json_decode($result['contact_properties'], true);
        }
        return $this->json([
            'success' => true,
            'data' => $results['results'],
        ]);
    }

    public function getChapterContacts(Request $request) {

        $chapterRecordId = $request->query->get('chapterRecordId', false);
        if(!$chapterRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'A chapter record Id must be passed up'
            ]);
        }

        try {
            $limit = $request->query->get('limit', null);
            $offset = $request->query->get('offset', null);
            $search = $request->query->get('search', null);
            $results = $this->recordRepository->getChapterContacts($chapterRecordId, $limit, $offset, $search);
        } catch (\Exception $exception) {
            if(empty($results['results'])) {
                return $this->json([
                    'success' => false
                ]);
            }
        }
        foreach($results["results"] as &$result) {
            $result['contact_properties'] = json_decode($result['contact_properties'], true);
            $result['chapter_properties'] = json_decode($result['chapter_properties'], true);
            $result['chapter_leadership_properties'] = json_decode($result['chapter_leadership_properties'], true);
        }
        return $this->json([
            'success' => true,
            'data' => $results['results'],
        ]);
    }

    public function getChapterEvents(Request $request) {

        $chapterRecordId = $request->query->get('chapterRecordId', false);
        if(!$chapterRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'A chapter record Id must be passed up'
            ]);
        }

        try {
            $limit = $request->query->get('limit', null);
            $offset = $request->query->get('offset', null);
            $search = $request->query->get('search', null);
            $results = $this->recordRepository->getChapterEvents($chapterRecordId, $limit, $offset, $search);
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
        ]);
    }

    public function eventRegister(Request $request) {

        $eventRecordId = $request->request->get('eventRecordId', false);
        $contactRecordId = $request->request->get('contactRecordId', false);
        $additionalGuests = $request->request->get('additionalGuests', 0);

        if(!$eventRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'An event record Id must be passed up'
            ]);
        }

        if(!$contactRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'A contact record Id must be passed up'
            ]);
        }

        $event = $this->recordRepository->find($eventRecordId);

        if(!$event) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Event not found for record id %s', $eventRecordId)
            ]);
        }

        $contact = $this->recordRepository->find($contactRecordId);
        if(!$contact) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Contact not found for record id %s', $contactRecordId)
            ]);
        }

        $portal = $this->portalRepository->findBy([
           'internalIdentifier' => '9874561920'
        ]);

        if(!$portal) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Portal not found for internal identifier %s', '9874561920')
            ]);
        }

        $customObject = $this->customObjectRepository->findOneBy([
            'internalName' => 'event_registration',
            'portal' => $portal
        ]);

        if(!$customObject) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Event Registration Custom Object not found for portal internal identifier %s', '9874561920')
            ]);
        }

        $properties = [];
        $properties['checked_in'] = "0";
        $properties['additional_guests'] = (int) $additionalGuests;
        $properties['contact'] = $contactRecordId;
        $properties['event'] = $eventRecordId;

        $record = new Record();
        $record->setCustomObject($customObject);
        $record->setProperties($properties);

        $this->entityManager->persist($record);
        $this->entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'User registered for event',
            'data' => [
                'recordId' => $record->getId(),
                'properties' => $record->getProperties()
            ]
        ]);
    }

    public function eventUnregister(Request $request) {

        $eventRegistrationRecordId = $request->request->get('eventRegistrationRecordId', false);
        if(!$eventRegistrationRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'An event registration record Id must be passed up'
            ]);
        }

        $eventRegistration = $this->recordRepository->find($eventRegistrationRecordId);

        if(!$eventRegistration) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Event registration not found for record id %s', $eventRegistrationRecordId)
            ]);
        }

        $this->entityManager->remove($eventRegistration);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'User successfully unregistered from event'
        ]);
    }

    public function eventRegistrationCheckIn(Request $request) {

        $eventRegistrationRecordId = $request->request->get('eventRegistrationRecordId', false);
        if(!$eventRegistrationRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'An event registration record Id must be passed up'
            ]);
        }

        $eventRegistration = $this->recordRepository->find($eventRegistrationRecordId);

        if(!$eventRegistration) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Event registration not found for record id %s', $eventRegistrationRecordId)
            ]);
        }

        $properties = $eventRegistration->getProperties();
        if(isset($properties['checked_in'])) {
            $properties['checked_in'] = "1";
        }

        $eventRegistration->setProperties($properties);
        $this->entityManager->persist($eventRegistration);
        $this->entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'User checked in successfully'
        ]);
    }

    public function eventRegistrationCancelCheckIn(Request $request) {

        $eventRegistrationRecordId = $request->request->get('eventRegistrationRecordId', false);
        if(!$eventRegistrationRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'An event registration record Id must be passed up'
            ]);
        }

        $eventRegistration = $this->recordRepository->find($eventRegistrationRecordId);

        if(!$eventRegistration) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Event registration not found for record id %s', $eventRegistrationRecordId)
            ]);
        }

        $properties = $eventRegistration->getProperties();
        if(isset($properties['checked_in'])) {
            $properties['checked_in'] = "0";
        }

        $eventRegistration->setProperties($properties);
        $this->entityManager->persist($eventRegistration);
        $this->entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'User check in successfully cancelled for event'
        ]);
    }

    public function eventNew(Request $request) {

        $eventName = $request->request->get('eventName', false);
        $startTime = $request->request->get('startTime', false);
        $endTime = $request->request->get('endTime', false);
        $startDate = $request->request->get('startDate', false);
        $endDate = $request->request->get('endDate', false);
        $type = $request->request->get('type', false);
        $chapterRecordId = $request->request->get('chapterRecordId', false);

        if(!$eventName || !$startTime || !$endTime || !$startDate || !$endDate || !$type || !$chapterRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'An event must have eventName, startTime, endTime, startDate, and endDate, type and chapter record id'
            ]);
        }

        $portal = $this->portalRepository->findBy([
            'internalIdentifier' => '9874561920'
        ]);

        if(!$portal) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Portal not found for internal identifier %s', '9874561920')
            ]);
        }

        $customObject = $this->customObjectRepository->findOneBy([
            'internalName' => 'event',
            'portal' => $portal
        ]);

        if(!$customObject) {
            return $this->json([
                'success' => false,
                'message' => sprintf('Event Custom Object not found for portal internal identifier %s', '9874561920')
            ]);
        }

        $eventRecord = new Record();
        $eventRecord->setCustomObject($customObject);
        $properties = [];
        $properties['name'] = $eventName;
        $properties['start_date'] = $startDate;
        $properties['end_date'] = $endDate;
        $properties['start_time'] = $startTime;
        $properties['end_time'] = $endTime;
        $properties['type'] = $type;
        $properties['chapter'] = $chapterRecordId;
        $eventRecord->setProperties($properties);

        $this->entityManager->persist($eventRecord);
        $this->entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'Event successfully created!',
            'data' => [
                'recordId' => $eventRecord->getId()
            ]
        ]);
    }

    public function eventEdit(Request $request, Record $eventRecord) {

        $eventName = $request->request->get('eventName', false);
        $startTime = $request->request->get('startTime', false);
        $endTime = $request->request->get('endTime', false);
        $startDate = $request->request->get('startDate', false);
        $endDate = $request->request->get('endDate', false);
        $type = $request->request->get('type', false);
        $chapterRecordId = $request->request->get('chapterRecordId', false);

        if(!$eventName || !$startTime || !$endTime || !$startDate || !$endDate || !$type || !$chapterRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'An event must have eventName, startTime, endTime, startDate, and endDate, type and chapter record id'
            ]);
        }

        $properties = [];
        $properties['name'] = $eventName;
        $properties['start_date'] = $startDate;
        $properties['end_date'] = $endDate;
        $properties['start_time'] = $startTime;
        $properties['end_time'] = $endTime;
        $properties['type'] = $type;
        $properties['chapter'] = $chapterRecordId;
        $eventRecord->setProperties($properties);

        $this->entityManager->persist($eventRecord);
        $this->entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'Event successfully updated!',
            'data' => [
                'recordId' => $eventRecord->getId()
            ]
        ]);
    }

    public function eventCancel(Request $request) {

        $eventRecordId = $request->request->get('eventRecordId', false);

        if(!$eventRecordId) {
            return $this->json([
                'success' => false,
                'message' => 'An event record id not found in database.'
            ]);
        }

        $eventRecord = $this->recordRepository->find($eventRecordId);

        $this->entityManager->remove($eventRecord);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Event successfully cancelled!',
        ]);

    }
}