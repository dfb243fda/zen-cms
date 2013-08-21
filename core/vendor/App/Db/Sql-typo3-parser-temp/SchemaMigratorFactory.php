<?php

namespace App\Db\Sql;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SchemaMigratorFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {        
        $appConfig = $serviceLocator->get('ApplicationConfig');        
        $config = $serviceLocator->get('Config');        
        $tmpConfig = isset($config['schemaMigration']) ? $config['schemaMigration'] : array();
        $tmpConfig['db'] = $serviceLocator->get('db');
        
        if (!isset($tmpConfig['tablePrefix'])) {
            $tmpConfig['tablePrefix'] = $appConfig['dbPref'];
        }
        
        return new SchemaMigrator($tmpConfig);
    }
}
