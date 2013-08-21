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
        $storage = $serviceManager->get('DBSessionStorage\Storage\DBStorage');
        $request = $serviceManager->get('request');
        $storage->setSessionStorage($request);
    }

    public function getConfig() 
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() 
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
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