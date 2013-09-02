<?php

namespace Installer;

use Zend\EventManager\EventInterface;

class Module
{    

    public function onBootstrap(EventInterface $event)
    {
        $app = $event->getTarget();
        $locator = $app->getServiceManager();
        
        $bootstrapper = $locator->get('Installer\Service\Bootstrapper');       
        $bootstrapper->bootstrap();       
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
}
