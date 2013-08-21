<?php

namespace App\UserManager;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserManagerServiceFactory implements FactoryInterface
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
        $tmpConfig = isset($config['userManager']) ? $config['userManager'] : array();
        $tmpConfig['serviceManager'] = $serviceLocator;
        
        return new UserManager($tmpConfig);
    }
}
