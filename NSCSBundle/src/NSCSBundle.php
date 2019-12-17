<?php

namespace NSCSBundle;

use NSCSBundle\DependencyInjection\NSCSExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class NSCSBundle
 * @package NSCSBundle
 * @see https://symfonycasts.com/screencast/symfony-bundle
 */
class NSCSBundle extends Bundle
{
    /**
     * Overridden to allow for the custom extension alias.
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new NSCSExtension();
        }
        return $this->extension;
    }
}