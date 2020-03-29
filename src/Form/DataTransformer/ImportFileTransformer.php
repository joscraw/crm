<?php

namespace App\Form\DataTransformer;

use App\Utils\ArrayHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\File;

class ImportFileTransformer implements DataTransformerInterface
{
    use ArrayHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * ImportFileTransformer constructor.
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Transforms an object (record) to a string (number).
     *
     * @param $text
     * @return string
     * @throws \Exception
     */
    public function transform($text)
    {

        if($text === null || empty($text)) {
            return '';
        }

        return $text;
    }

    /**
     * @param mixed $encodedFile
     * @return mixed|File|null
     */
    public function reverseTransform($encodedFile)
    {
        if (empty($encodedFile) || $encodedFile === null) {
            return null;
        }

        $tmpPath = sys_get_temp_dir().'/'.uniqid();
        file_put_contents($tmpPath, base64_decode($encodedFile));
        $uploadedFile = new File($tmpPath);

        return $uploadedFile;
    }
}