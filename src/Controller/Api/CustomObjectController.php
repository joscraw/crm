<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Form\CustomObjectType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Model\CustomObjectField;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Service\MessageGenerator;
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
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class CustomObjectController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/objects")
 */
class CustomObjectController extends ApiController
{
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * CustomObjectController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->serializer = $serializer;
    }


    /**
     * @Route("/", name="get_custom_objects", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getCustomObjectsAction(Portal $portal, Request $request) {

        $customObjects = $this->customObjectRepository->findBy([
            'portal' => $portal->getId()
        ]);

        $payload = [];
        $payload['custom_objects'] = [];

        foreach($customObjects as $customObject) {
            $json = $this->serializer->serialize($customObject, 'json', ['groups' => ['CUSTOM_OBJECTS_FOR_FILTER']]);

            $payload['custom_objects'][] = json_decode($json, true);
        }

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload,
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * This doesn't return a list of all possible merge tags. Just 1 level deep.
     * The user can go deeper with the extraction depending on the data they want to use
     *
     * @Route("/{internalName}/merge-tags", name="get_merge_tags", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getMergeTags(Portal $portal, CustomObject $customObject, Request $request) {

        $mergeTags = [];
        foreach($customObject->getProperties() as $property) {
            $parentTag = $property->getInternalName();
            if($property->getFieldType() === FieldCatalog::CUSTOM_OBJECT) {
                /** @var CustomObjectField $customObjectField */
                $customObjectField = $property->getField();
                foreach($customObjectField->getCustomObject()->getProperties() as $property) {
                    $mergeTags[] = sprintf("{%s.%s}", $parentTag, $property->getInternalName());
                }
            } else {
                $mergeTags[] = sprintf("{%s}", $parentTag);
            }
        }

        return new JsonResponse([
            'success' => true,
            'data'  => $mergeTags
        ], Response::HTTP_OK);
    }
}