<?php

namespace Templates;

use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        
        $eventManager = $app->getEventManager();
        
        $moduleTemplatesCollector = $locator->get('Templates\Listener\ModuleTemplatesCollector');
        $eventManager->attach($moduleTemplatesCollector);
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
        $installer = $sm->get('Templates\Service\Installer');
        $installer->install();
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    } 
}