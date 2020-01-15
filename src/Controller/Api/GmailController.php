<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\GmailAttachment;
use App\Entity\GmailMessage;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Form\CustomObjectType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Service\GmailProvider;
use App\Service\MessageGenerator;
use App\Service\UploaderHelper;
use App\Utils\ServiceHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_Gmail;
use League\Flysystem\FilesystemInterface;
use PhpMimeMailParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class GoogleController
 * @package App\Controller
 *
 * @Route("{internalIdentifier}/api/gmail")
 *
 */
class GmailController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/messages/attachments/{fileName}/download", name="gmail_download_message_attachment", methods={"GET"}, options = { "expose" = true })
     * @param $fileName
     * @param UploaderHelper $uploaderHelper
     * @param FilesystemInterface $privateUploadsFilesystem
     * @param FilesystemInterface $tmpDirectoryFilesystem
     * @return StreamedResponse
     */
    public function downloadMessageAttachment($fileName, UploaderHelper $uploaderHelper, FilesystemInterface $privateUploadsFilesystem, FilesystemInterface $tmpDirectoryFilesystem)
    {
        $gmailAttachment = $this->gmailAttachmentRepository->findOneBy([
           'fileName' => $fileName
        ]);

        /*$this->denyAccessUnlessGranted('download_attachment', $chatMessage);*/
        $response = new StreamedResponse(function() use ($gmailAttachment, $uploaderHelper, $privateUploadsFilesystem, $tmpDirectoryFilesystem) {
            $outputStream = fopen('php://output', 'wb');
            $stream = $uploaderHelper->readStream($gmailAttachment->getAttachmentFilePath(), false);
            if ($stream === false) {
                throw new \Exception(sprintf('Error opening stream for "%s"', $gmailAttachment->getAttachmentFilePath()));
            }
            stream_copy_to_stream($stream, $outputStream);
        });
        $response->headers->set('Content-Type', $gmailAttachment->getMimeType());
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $gmailAttachment->getOriginalFileName()
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}