<?php

namespace ImageGallery;

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
        if (!$sm->has('ImageGallery\Service\Installer')) {
            require_once __DIR__ . '/src/ImageGallery/Service/Installer.php';
            $sm->setInvokableClass('ImageGallery\Service\Installer', 'ImageGallery\Service\Installer');
        }
        
        $installerService = $sm->get('ImageGallery\Service\Installer');
        $installerService->install();
    }
}