<?php

namespace App\FileManager;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FileManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {        
        $config = $serviceLocator->get('Config');        
        $tmpConfig = isset($config['fileManager']) ? $config['fileManager'] : array();
        
        return new FileManager($tmpConfig);
    }
}
