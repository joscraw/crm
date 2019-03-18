<?php

namespace App\Request\ParamConverter;

use App\Entity\Property;
use App\Entity\Record;
use App\Entity\Report;
use App\Entity\Role;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class RoleConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * RoleConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param RoleRepository $roleRepository
     */
    public function __construct(EntityManagerInterface $entityManager, RoleRepository $roleRepository)
    {
        $this->entityManager = $entityManager;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $roleId = $request->attributes->get('roleId');

        $role = $this->roleRepository->find($roleId);

        if(!$role) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $role);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {

        if($configuration->getClass() !== Role::class) {
            return false;
        }

        return true;
    }
}