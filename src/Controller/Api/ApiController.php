<?php


namespace App\Controller\Api;


use App\Controller\Exception\InvalidInputException;
use App\Controller\Exception\MissingRequiredQueryParameterException;
use App\Entity\CustomObject;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller\Api
 *
 */
class ApiController extends AbstractController
{
    /**
     * Get Custom Object (from request parameters)
     *
     * @param CustomObjectRepository $customObjectRepository
     * @param bool|true $required
     * @return CustomObject|null
     * @throws InvalidInputException
     * @throws MissingRequiredQueryParameterException
     */
    protected function getCustomObjectForRequest(
        CustomObjectRepository $customObjectRepository,
        $required = true
    ) {

        $customObject = $this->getEntityForRequest(
            $customObjectRepository,
            Constants::ARG_CUSTOM_OBJECT_ID,
            Constants::ERR_MSG_CUSTOM_OBJECT_NOT_FOUND,
            $required
        );

        if ($required && null === $customObject) {
            throw new InvalidInputException(
                Constants::ERR_MSG_CUSTOM_OBJECT_NOT_FOUND,
                Constants::ERROR_CODE_NOT_FOUND_CUSTOM_OBJECT
            );
        }

        return $customObject;
    }

    /**
     * Get Property (from request parameters)
     *
     * @param PropertyRepository $propertyRepository
     * @param bool|true $required
     * @return CustomObject|null
     * @throws InvalidInputException
     * @throws MissingRequiredQueryParameterException
     */
    protected function getPropertyForRequest(
        PropertyRepository $propertyRepository,
        $required = true
    ) {

        $property = $this->getEntityForRequest(
            $propertyRepository,
            Constants::ARG_PROPERTY_ID,
            Constants::ERR_MSG_PROPERTY_NOT_FOUND,
            $required
        );

        if ($required && null === $property) {
            throw new InvalidInputException(
                Constants::ERR_MSG_PROPERTY_NOT_FOUND,
                Constants::ERROR_CODE_NOT_FOUND_PROPERTY
            );
        }

        return $property;
    }

    /**
     * Get an entity (from request parameters)
     *
     * @param EntityRepository $repository
     * @param $entityIdParameterName
     * @param $entityNotFoundMsg
     * @param bool|true $required
     * @return null|object
     * @throws MissingRequiredQueryParameterException
     */
    protected function getEntityForRequest(
        EntityRepository $repository,
        $entityIdParameterName,
        $entityNotFoundMsg,
        $required
    ) {
        $entityId = $this->getArgument(
            $entityIdParameterName,
            $required
        );

        $entity = null;
        if ($entityId) {
            $entity = $repository->find($entityId);
        }

        if ($required && null === $entity) {
            // @TODO Should this throw the exception?
            $exception = $this->createNotFoundException($entityNotFoundMsg);
        }

        return $entity;
    }

    /**
     * @param $argumentName
     * @param bool|true $required
     * @return string|null
     * @throws MissingRequiredQueryParameterException
     */
    protected function getArgument(
        $argumentName,
        $required = true
    ) {
        $requestStack   = $this->get('request_stack');
        $currentRequest = $requestStack->getCurrentRequest();

        $argumentValue = $currentRequest->get($argumentName);
        if ($required && null === $argumentValue) {
            $exception = new MissingRequiredQueryParameterException(
                Constants::ERR_MSG_MISSING_ARG . $argumentName,
                Constants::ERROR_CODE_MISSING_ARG
            );
            throw $exception;
        }
        return $argumentValue;
    }
}