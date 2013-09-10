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
    
    public function onInstall($sm)
    {
        $installer = $sm->get('Users\Service\Installer');
        $installer->install();
    }
    
    public function getDynamicConfig($sm)
    {
        $form = $sm->get('Users\Form\DynamicConfigForm');
        return array(
            'form' => array(
                'registration' => $form,
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'users_auth_service' => function ($sm) {
                    return new \Zend\Authentication\AuthenticationService(
                        $sm->get('Users\Authentication\Storage\Db'),
                        $sm->get('Users\Authentication\Adapter\AdapterChain')
                    );
                },
                'users_mapper' => function ($sm) {
                    $config = $sm->get('config');
                    $usersConfig = $config['Users'];

                    $mapper = new Mapper\User();
                    $mapper->setDbAdapter($sm->get('db'));
                    $entityClass = $usersConfig['userEntityClass'];
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\UserHydrator());
                    $mapper->setTableName($usersConfig['tableName']);
                    return $mapper;
                },   
            ),
        );
    }
    
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'UserAuthentication' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();
                    $authService = $serviceLocator->get('users_auth_service');
                    $authAdapter = $serviceLocator->get('Users\Authentication\Adapter\AdapterChain');
                    $controllerPlugin = new Controller\Plugin\UserAuthentication();
                    $controllerPlugin->setAuthService($authService);
                    $controllerPlugin->setAuthAdapter($authAdapter);
                    return $controllerPlugin;
                },
            ),
        );
    }
    
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'userDisplayName' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserDisplayName;
                    $viewHelper->setAuthService($locator->get('users_auth_service'));
                    return $viewHelper;
                },
                'userIdentity' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserIdentity;
                    $viewHelper->setAuthService($locator->get('users_auth_service'));
                    return $viewHelper;
                },    
            ),   
        );

    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    } 
}