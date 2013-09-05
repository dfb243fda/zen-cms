<?php

namespace Search;

use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        $eventManager = $locator->get('application')->getEventmanager();   
        
        $moduleSearchObjectTypesCollector = $locator->get('Search\Listener\ModuleSearchObjectTypesCollector');
        $eventManager->attach($moduleSearchObjectTypesCollector);
    }
    
    public function onInstall($sm)
    {
        $installer = $sm->get('Search\Service\Installer');
        $installer->install();
    }
    
    public function onUninstall($sm)
    {
        $db = $sm->get('db');
        
        $db->query("DROP TABLE IF EXISTS `" . DB_PREF . "search_object_types`", array());
        $db->query("DROP TABLE IF EXISTS `" . DB_PREF . "search_index`", array());
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }
}