<?php

namespace Logger;

use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getTarget();
        $locator = $this->serviceManager = $app->getServiceManager();
        
        $logger = $locator->get('Logger\Service\Logger');
        $logger->init();        
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getTablesSql()
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
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
