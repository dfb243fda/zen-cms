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
        $loginzaForm = array(
            'fieldsets' => array(                    
                array(
                    'spec' => array(
                        'name' => 'loginza',
                        'options' => array(
                            'label' => 'opa',
          //                  'use_as_base_fieldset' => false,
                        ),
                        'elements' => array(
                            array(
                                'spec' => array(
                                    'type' => 'Zend\Form\Element\Collection',
                                    'name' => 'domains',
                                    'options' => array(
                                        'count' => 2,
                                        'should_create_template' => true,
                                        'allow_add' => true,
                                        'target_element' => array(
                                            'type' => 'Users\Fieldset\LoginzaFieldset'
                                        )
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        
        
        
        $formElementManager = $sm->get('FormElementManager');
        
        $form = $formElementManager->get('Users\Form\DynamicConfigForm');
        return array(
            'form' => array(
                'loginza' => $loginzaForm,
                'registration' => $form,
            ),
        );
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    } 
}