<?php

namespace App\Service;

use Exception;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;

class ImageCacheGenerator
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var FilterConfiguration
     */
    private $filterConfiguration;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param CacheManager $cacheManager
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param FilterConfiguration $filterConfiguration
     * @param Logger $logger
     */
    public function __construct(CacheManager $cacheManager, DataManager $dataManager, FilterManager $filterManager, FilterConfiguration $filterConfiguration, Logger $logger)
    {
        $this->cacheManager        = $cacheManager;
        $this->dataManager         = $dataManager;
        $this->filterManager       = $filterManager;
        $this->filterConfiguration = $filterConfiguration;
        $this->logger = $logger;
    }

    public function cacheImageForAllFilters($path)
    {
        foreach ($this->filterConfiguration->all() as $context => $filterConfig) {
            $this->cacheImage($path, $context);
        }
    }

    public function removeCachedImagesForAllFilters($path) {
        foreach ($this->filterConfiguration->all() as $context => $filterConfig) {
            $this->removeCachedImage($path, $context);
        }
    }

    public function removeCachedImage($path, $filter)
    {
        $this->cacheManager->remove($path, $filter);
    }

    public function cacheImage($path, $filter)
    {
        try {
            $imageBinary = $this->dataManager->find($filter, $path);

            $this->cacheManager->store(
                $this->filterManager->applyFilter($imageBinary, $filter),
                $path,
                $filter
            );

        } catch (Exception $ex) {
            $error = 'An error occurred while caching the image: ' . $path;
            $this->logger->error($error, array('exception' => (string) $ex));
            throw new Exception($error, 0, $ex);
        }
    }
}