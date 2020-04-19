<?php

namespace App\Controller\PrivateApi;

use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AlbumController
 * @package App\Controller\PrivateApi
 * @Route("/api/private/albums")
 */
class AlbumController extends AbstractController
{
    /**
     * @Route("", name="private_api_albums")
     * @return JsonResponse
     */
    public function index()
    {
        throw new ApiException(new ApiErrorResponse(
            null,
            ApiErrorResponse::TYPE_VALIDATION_ERROR,
            [],
            Response::HTTP_BAD_REQUEST
        ));

        $user = $this->getUser();

        $data = [
            [
                'albumId' => "1",
                "id" => 1,
                "title" => "accusamus beatae ad facilis cum similique qui sunt",
                "description" => "It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout"
            ],
            [
                'albumId' => "2",
                "id" => 2,
                "title" => "accusamus beatae ad facilis cum similique qui sunt",
                "description" => "Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text"
            ],
            [
                'albumId' => "3",
                "id" => 3,
                "title" => "accusamus beatae ad facilis cum similique qui sunt",
                "description" => "There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form"
            ],
        ];

        return new JsonResponse($data);
    }

}