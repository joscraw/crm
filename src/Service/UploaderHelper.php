<?php

namespace App\Service;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;

class UploaderHelper
{
    const SPREADSHEET = 'spreadsheet';
    const ATTACHMENT = 'attachment';

    /**
     * @var RequestStackContext
     */
    private $requestStackContext;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var FilesystemInterface
     */
    private $privateFilesystem;

    /**
     * @var string
     */
    private $uploadsPath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $publicAssetBaseUrl;

    public function __construct(FilesystemInterface $publicUploadsFilesystem, FilesystemInterface $privateUploadsFilesystem, RequestStackContext $requestStackContext, $uploadsPath, LoggerInterface $logger, $uploadedAssetsBaseUrl)
    {
        $this->requestStackContext = $requestStackContext;
        $this->filesystem = $publicUploadsFilesystem;
        $this->privateFilesystem = $privateUploadsFilesystem;
        $this->uploadsPath = $uploadsPath;
        $this->logger = $logger;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    public function uploadSpreadsheet(File $file) {

        return $this->uploadFile($file, self::SPREADSHEET, true);

    }

    public function uploadAttachment(File $file) {

        return $this->uploadFile($file, self::ATTACHMENT, false);

    }

    private function uploadFile(File $file, string $directory, bool $isPublic)
    {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $extension = $this->guessExtension($file);

        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.uniqid().'.'.$extension;
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;
        $stream = fopen($file->getPathname(), 'r');
        $result = $filesystem->writeStream(
            $directory.'/'.$newFilename,
            $stream
        );
        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }
        if (is_resource($stream)) {
            fclose($stream);
        }
        return $newFilename;
    }

    /**
     * For security reasons symfony uses the following method to determine file extension
     * https://www.tutorialfor.com/questions-41236.htm
     * This can cause us issues on determining extension and mime type for CSV file
     *
     * @param File $file
     * @return string|null
     */
    public function guessExtension(File $file) {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        // For security reasons symfony uses the following method to determine file extension
        // https://www.tutorialfor.com/questions-41236.htm
        // This can cause issues guessing whether or not it's a csv file
        if(pathinfo (basename ($originalFilename)) ['extension'] === 'csv') {
            $extension = 'csv';
        } else {
            $extension = $file->guessExtension();
        }
        return $extension;
    }

    /**
     * @param string $path
     * @param bool $isPublic
     * @return resource
     * @throws FileNotFoundException
     */
    public function readStream(string $path, bool $isPublic)
    {
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;
        $resource = $filesystem->readStream($path);
        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }
        return $resource;
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext->getBasePath() . '/uploads/'.$path;
    }

    public function getUploadsPath() {
        return $this->uploadsPath;
    }
}