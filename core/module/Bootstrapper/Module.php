<?php

namespace Bootstrapper;

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
        
        $constants = $locator->get('Bootstrapper\Service\Constants');
        
        $constants->defineConstants();
        $this->setLocale();
        $this->setPhpIniSettings();
        $this->setTimeZone();        
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getTablesSql()
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }
    
    public function getDynamicConfig($sm)
    {
        $formElementManager = $sm->get('FormElementManager');
        
        $systemConfigForm = $formElementManager->get('Bootstrapper\Form\SystemConfigForm');  
        
        return array(
            'form' => array(
                'general' => $systemConfigForm,
            ),
        );
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
    
    public function onInstall($sm)
    {
        $configManager = $sm->get('configManager');
        
        $configManager->set('system', 'date_format', 'd-m-Y');
        $configManager->set('system', 'js_date_format', 'dd-mm-yy');
        $configManager->set('system', 'time_format', 'H:i');
        $configManager->set('system', 'js_time_format', 'HH:mm');
                
        $configManager->set('system', 'timezone', date_default_timezone_get());
    }
    
    protected function setLocale()
    {
        $configManager = $this->serviceManager->get('configManager');
        $this->serviceManager->get('translator')->setLocale($configManager->get('system', 'language'));
    }
    
    protected function setPhpIniSettings()
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');        
        $phpSettings = $appConfig['phpSettings'];
        foreach ($phpSettings as $key => $value) {
            ini_set($key, $value);
        }
    }
    
    protected function setTimeZone()
    {
        $configManager = $this->serviceManager->get('configManager');
        if ($configManager->has('system', 'timezone')) {
            date_default_timezone_set($configManager->get('system', 'timezone'));
        } 
    }
}
