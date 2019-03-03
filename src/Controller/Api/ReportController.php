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
use App\Utils\ArrayHelper;
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
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class ReportController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/reports")
 */
class ReportController extends ApiController
{
    use MultiDimensionalArrayExtractor;
    use ArrayHelper;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * PropertySettingsController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/{internalName}/save-report", name="save_report", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function saveReportAction(Portal $portal, CustomObject $customObject, Request $request) {

        $data = $request->request->get('data', []);

        $results = $this->recordRepository->getReportData($data, $customObject);



        // going to be deleting a lot of this



        $customObjectInternalNames = $this->propertyRepository->findAllInternalNamesByFieldTypeForCustomObject($customObject, FieldCatalog::CUSTOM_OBJECT);
        $customObjectInternalNames = $this->getArrayValuesRecursive($customObjectInternalNames);

        $properties = $customObject->getProperties()->toArray();

        foreach($results['results'] as &$result) {

            foreach($result as $key => $value) {

                $customObjectProperty = array_filter($properties, function($property) use($key) {
                    $isCustomObjectProperty = $property->getFieldType() === FieldCatalog::CUSTOM_OBJECT;
                    $internalNameMatches = $property->getInternalName() === $key;

                    return $isCustomObjectProperty && $internalNameMatches;
                });

                if(!empty($customObjectProperty)) {
                    $customObjectProperty = array_values($customObjectProperty);

                    if(in_array($value, ['-', ''])) {
                        continue;
                    }

                    $value = json_decode($value);
                    $value = is_array($value) ? $value : [$value];

                    $urls = [];
                    foreach($value as $v) {
                        $url = sprintf("%s/%s",
                            $this->generateUrl('record_list', [
                                'internalIdentifier' => $portal->getInternalIdentifier(),
                                'internalName' => $customObjectProperty[0]->getField()->getCustomObject()->getInternalName()
                            ]),
                            $v
                        );
                        $urls[] = "<a href='$url'>$v</a>";
                    }
                    $result[$key] = implode(',', $urls);
                }

                $choiceFieldProperty = array_filter($properties, function($property) use($key) {
                    $isChoiceFieldProperty = $property->getFieldType() === FieldCatalog::MULTIPLE_CHECKBOX;
                    $internalNameMatches = $property->getInternalName() === $key;

                    return $isChoiceFieldProperty && $internalNameMatches;
                });

                if(!empty($choiceFieldProperty)) {

                    if(in_array($value, ['-', ''])) {
                        continue;
                    }

                    $value = json_decode($value);
                    $value = is_array($value) ? $value : [$value];

                    $items = [];
                    foreach($value as $v) {
                        $items[] = $v;
                    }
                    $result[$key] = implode(',', $items);
                }
            }
        }

        $countQuery = $this->recordRepository->findCountByCustomObject($customObject);
        $totalRecordsCount = !empty($countQuery[0]['count']) ? $countQuery[0]['count'] : 0;

        $results = $results['results'];
        $filteredRecordsCount = count($results);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) || !empty($customFilters) ? $filteredRecordsCount : $totalRecordsCount,
            'recordsTotal'  => $totalRecordsCount,
            'data'  => $results
        ],  Response::HTTP_OK);

        return $response;
    }
}