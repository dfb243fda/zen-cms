<?php

namespace Menu;

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
        if (!$sm->has('Menu\Service\Installer')) {
            require_once __DIR__ . '/src/Menu/Service/Installer.php';
            $sm->setInvokableClass('Menu\Service\Installer', 'Menu\Service\Installer');
        }
        
        $installerService = $sm->get('Menu\Service\Installer');
        $installerService->install();
    }
}