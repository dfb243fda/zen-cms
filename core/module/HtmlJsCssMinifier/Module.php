<?php

namespace HtmlJsCssMinifier;

class Module
{
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
            ),
        ));
    }  
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onInstall($sm)
    {
        require_once __DIR__ . '/src/' . __NAMESPACE__ . '/Service/Installer.php';
        if (!$sm->has('HtmlJsCssMinifier\Service\Installer')) {
            $sm->setInvokableClass('HtmlJsCssMinifier\Service\Installer', 'HtmlJsCssMinifier\Service\Installer');
        }        
        $moduleInstaller = $sm->get('HtmlJsCssMinifier\Service\Installer');
        $moduleInstaller->install();  
    }
    
    public function onBootstrap($e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();        
        
        $eventManager = $locator->get('application')->getEventmanager();  
                             
        $publicResourcesComposer = $locator->get('HtmlJsCssMinifier\Listener\PublicResourcesComposer'); 
        $eventManager->attach($publicResourcesComposer);
        
        $htmlMinifier = $locator->get('HtmlJsCssMinifier\Listener\HtmlMinifier'); 
        $eventManager->attach($htmlMinifier);
    }
}
