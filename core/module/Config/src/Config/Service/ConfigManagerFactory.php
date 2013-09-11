<?php
namespace Config\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('db');
        return new ConfigManager($db);
    }
}