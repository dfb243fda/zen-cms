<?php

namespace ObjectTypes;


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
    
    public function onInstall($sm)
    {
        require_once __DIR__ . '/src/' . __NAMESPACE__ . '/Service/Installer.php';
        $sm->setInvokableClass('ObjectTypes\Service\Installer', 'ObjectTypes\Service\Installer');
        $moduleInstaller = $sm->get('ObjectTypes\Service\Installer');
        $moduleInstaller->install();  
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }      
}
