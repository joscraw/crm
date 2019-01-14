<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Record;
use App\Form\CustomObjectType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Form\RecordType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Service\MessageGenerator;
use App\Utils\MultiDimensionalArrayExtractor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


/**
 * Class RecordController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/records")
 */
class RecordController extends ApiController
{
    use MultiDimensionalArrayExtractor;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * PropertySettingsController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
    }

    /**
     * @Route("/create-form", name="create_record_form", methods={"GET"}, options = { "expose" = true })
     * @return JsonResponse
     * @throws \App\Controller\Exception\InvalidInputException
     * @throws \App\Controller\Exception\MissingRequiredQueryParameterException
     */
    public function getRecordFormAction() {

        $customObject = $this->getCustomObjectForRequest($this->customObjectRepository);

        $properties = $this->propertyRepository->findBy([
            'customObject' => $customObject->getId()
        ]);

        $form = $this->createForm(RecordType::class, null, [
            'properties' => $properties
        ]);

        $formMarkup = $this->renderView(
            'Api/form/record_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/create", name="create_record", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Controller\Exception\InvalidInputException
     * @throws \App\Controller\Exception\MissingRequiredQueryParameterException
     */
    public function createRecordAction(Portal $portal, Request $request) {

        $customObject = $this->getCustomObjectForRequest($this->customObjectRepository);

        $properties = $this->propertyRepository->findBy([
            'customObject' => $customObject->getId()
        ]);

        $form = $this->createForm(RecordType::class, null, [
            'properties' => $properties
        ]);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/record_form.html.twig',
                [
                    'form' => $form->createView(),
                ]
            );

            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $formMarkup,
                ], Response::HTTP_BAD_REQUEST
            );
        }

        $record = new Record();
        $properties = $form->getData();
        $record->setProperties($properties);
        $record->setCustomObject($customObject);

        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/selectize", name="records_for_selectize", methods={"GET"}, options = { "expose" = true })
     * @see https://stackoverflow.com/questions/29444430/remote-data-loading-from-sql-with-selectize-js
     * @see https://selectize.github.io/selectize.js/
     * @param Request $request
     * @return Response
     * @throws \App\Controller\Exception\InvalidInputException
     * @throws \App\Controller\Exception\MissingRequiredQueryParameterException
     */
    public function getRecordsForSelectizeAction(Request $request) {

        $customObject = $this->getCustomObjectForRequest($this->customObjectRepository);

        $search = $request->query->get('search');
        $allowedCustomObjectToSearch = $this->customObjectRepository
            ->find($request->query->get('allowed_custom_object_to_search'));

        $records = $this->recordRepository->getSelectizeData($search, $allowedCustomObjectToSearch);

        foreach($records as &$record) {
            $record['valueField'] = $record['id'];
            // Add the record id to the searchField so we can search by that also
            // use the current time as the key so you don't run any risk of overriding any of
            // the record property key/values
            $record['properties']['id'] = $record['id'];
            $record['searchField'] = json_encode($this->extractValues($record['properties']));
            $record['labelField'] = sprintf("%s id: %s",
                $allowedCustomObjectToSearch->getLabel(),
                $record['id']
            );
        }

        $response = new JsonResponse($records,  Response::HTTP_OK);
        return $response;
    }
}