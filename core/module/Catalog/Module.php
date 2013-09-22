<?php

namespace Catalog;

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
        if (!$sm->has('Catalog\Service\Installer')) {
            require_once __DIR__ . '/src/Catalog/Service/Installer.php';
            $sm->setInvokableClass('Catalog\Service\Installer', 'Catalog\Service\Installer');
        }
        
        $installerService = $sm->get('Catalog\Service\Installer');
        $installerService->install();
    }
}