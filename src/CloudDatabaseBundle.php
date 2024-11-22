<?php

namespace JorisRos\CloudDatabaseBundle;

use JorisRos\CloudDatabaseBundle\DependencyInjection\BoilerPlateBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CloudDatabaseBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            return new BoilerPlateBundleExtension();
        }

        return $this->extension;
    }
}