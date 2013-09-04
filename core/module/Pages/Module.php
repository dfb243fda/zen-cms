<?php

namespace Pages;

use Zend\Mvc\MvcEvent;

class Module 
{    
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        
        $eventManager = $locator->get('application')->getEventmanager();    
        
        $modulePageTypesCollector = $locator->get('Pages\Listener\ModulePageTypesCollector');                    
        $eventManager->attach($modulePageTypesCollector);
        
        $moduleContentTypesCollector = $locator->get('Pages\Listener\ModuleContentTypesCollector');                    
        $eventManager->attach($moduleContentTypesCollector);
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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onInstall($sm)
    {
        $moduleInstaller = $sm->get('Pages\Service\Installer');
        $moduleInstaller->install();        
    }
    
    public function getTablesSql()
    {               
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }    
}
