<?php

namespace App\PublicResources;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PublicResourcesServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {        
        $config = $serviceLocator->get('Config');        
        $tmpConfig = isset($config['publicResources']) ? $config['publicResources'] : array();
        $tmpConfig['serviceManager'] = $serviceLocator;
        
        return new PublicResources($tmpConfig);
    }
}
