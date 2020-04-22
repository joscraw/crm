<?php

namespace App\Controller\PrivateApi\V2;

use App\Entity\User;
use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Utils\ServiceHelper;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\CustomObject;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Controller\PrivateApi\V1\CustomObjectController as CustomObjectController_V1;

/**
 * Class AlbumController
 * @package App\Controller\PrivateApi
 *
 */
class CustomObjectController extends CustomObjectController_V1
{
    use ServiceHelper;

}