<?php

namespace Users;

class Module
{
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
    
    public function onBootstrap($e)
    {
        $app = $e->getTarget();        
        $locator = $app->getServiceManager();
        $logger = $locator->get('logger');
        
        $logger->addProcessor('App\Log\Processor\User', 1, array(
            'userData' => $locator->get('users_auth_service')->getIdentity(),
        ));
    }
    
    public function getConfig($env = null)
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'users_auth_service' => function ($sm) {
                    return new Authentication\AuthenticationService($sm);
                },
                'Users\Service\AuthenticationAdapters' => function($sm) {
                    $config = $sm->get('config');                    
                    return new Service\AuthenticationAdapters($config['Users']['authAdapters']);
                },                   
            ),
        );
    }
    
    public function onInstall($sm)
    {
        $installer = $sm->get('Users\Service\Installer');
        $installer->install();
    }
    
    public function getDynamicConfig($sm)
    {           
        $formElementManager = $sm->get('FormElementManager');
        
        $loginzaForm = $formElementManager->get('Users\Form\LoginzaConfigForm');        
        $registrationForm = $formElementManager->get('Users\Form\RegistrationConfigForm');
        
        return array(
            'form' => array(
                'loginza' => $loginzaForm,
                'registration' => $registrationForm,
            ),
        );
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    } 
}