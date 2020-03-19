<?php

namespace App\Controller\Api;

use App\Entity\GmailAccount;
use App\Entity\GmailAttachment;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UploaderHelper;
use App\Utils\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Asset\Packages;

/**
 * Class FileController
 * @package App\Controller
 * @Route("/api/files")
 */
class FileController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/upload", name="upload_files", options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function uploadAction(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UploadedFile $image */
        $images = [];
        $runtimeConfig = array(
        	'thumbnail' => array(
        		'mode' => 'inset',
	        ),
        );
        foreach($request->files->all() as $key => $image) {
            $newFilename = $this->uploaderHelper->upload($image, UploaderHelper::IMAGE);
            $path = $this->uploaderHelper->getPublicPath(UploaderHelper::IMAGE) .'/'. $newFilename;
            $this->imageCacheGenerator->cacheImageForAllFilters($path);
            $images[] = [
                'url' => $this->cacheManager->getBrowserPath('uploads/'.UploaderHelper::IMAGE.'/'.$newFilename, 'squared_thumbnail_small', $runtimeConfig ),
                'path' => $path,
            ];
        }
        if(!empty($images)) {
            return new JsonResponse(
                [
                    'success' => true,
                    'images' => $images,
                ], Response::HTTP_OK
            );
        }
        return new JsonResponse(
            [
                'success' => false,
            ], Response::HTTP_BAD_REQUEST
        );
    }
}