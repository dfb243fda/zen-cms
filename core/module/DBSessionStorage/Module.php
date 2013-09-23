<?php

/**
 * This file is part of the DBSessionStorage Module (https://github.com/Nitecon/DBSessionStorage.git)
 *
 * Copyright (c) 2013 Will Hattingh (https://github.com/Nitecon/DBSessionStorage.git)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.txt that was distributed with this source code.
 */

namespace DBSessionStorage;

class Module {

    public function onBootstrap(\Zend\Mvc\MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $storage = $serviceManager->get('DBSessionStorage\DBStorage');
        $request = $serviceManager->get('request');
        $storage->setSessionStorage($request);
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'DBSessionStorage\DBStorage' => function($sm) {
                    $appConfig = $sm->get('ApplicationConfig');                    
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');                      
                    $dbPref = $appConfig['dbPref'];
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $sessionConfig = $config['session'];
                    } else {
                        $sessionConfig = array();
                    }                    
                    
                    $storage = new DBStorage($dbAdapter, $dbPref, $sessionConfig);
                    return $storage;
                },
            ),
        );
    }

    public function getConfig() 
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() 
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }
}