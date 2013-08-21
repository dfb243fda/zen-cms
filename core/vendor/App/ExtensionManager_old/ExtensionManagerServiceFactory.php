<?php

namespace App\ExtensionManager;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExtensionManagerServiceFactory implements FactoryInterface
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
        $tmpConfig = isset($config['extensionManager']) ? $config['extensionManager'] : array();
        $tmpConfig['serviceManager'] = $serviceLocator;
        
        return new ExtensionManager($tmpConfig);
    }
}
