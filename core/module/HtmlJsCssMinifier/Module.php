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
        $moduleInstaller = $sm->get('HtmlJsCssMinifier\Service\Installer');
        $moduleInstaller->install();  
    }
    
    public function onBootstrap($e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        
        $onBootstrapListener = $locator->get('HtmlJsCssMinifier\Listener\OnBootstrap'); 
        $eventManager = $locator->get('application')->getEventmanager();        
        $appConfig = $locator->get('ApplicationConfig');
                
        define('MUNEE_CACHE', $appConfig['module_listener_options']['cache_dir'] . '/munee');        
              
        $eventManager->attach($onBootstrapListener);
    }
}
