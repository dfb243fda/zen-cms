<?php

namespace Rbac;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ServiceManager\AbstractPluginManager;

class Module implements
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ControllerPluginProviderInterface,
    ViewHelperProviderInterface
{    
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        $app            = $event->getTarget();
        $serviceManager = $app->getServiceManager();
                
        $guards         = $serviceManager->get('Rbac\Guards');
        foreach ($guards as $guard) {
            $app->getEventManager()->attach($guard);
        }
        
        $config         = $serviceManager->get('config');
        $strategy = $serviceManager->get($config['Rbac']['unauthorized_strategy']);
        $app->getEventManager()->attach($strategy);
        
        $displayErrorsSetter = $serviceManager->get('Rbac\Service\DisplayErrorsSetter');
        $displayErrorsSetter->setDisplayErrors();
        
        $newUserRolesSetter = $serviceManager->get('Rbac\Listener\NewUserRolesSetter');
        $app->getEventManager()->attach($newUserRolesSetter);
        
        $modulePermissionsCollector = $serviceManager->get('Rbac\Listener\ModulePermissionsCollector');
        $app->getEventManager()->attach($modulePermissionsCollector);             
    }
    
    public function getDynamicConfig($sm)
    {
        $dynamicConfigService = $sm->get('Rbac\Service\DynamicConfig');
        return $dynamicConfigService->getConfig();
    }

    /**
     * {@inheritDoc}
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'isAllowed' => function (AbstractPluginManager $pluginManager) {
                    $serviceLocator = $pluginManager->getServiceLocator();
                    /* @var $authorize \BjyAuthorize\Service\Authorize */
                    $authorize = $serviceLocator->get('Rbac\Service\Authorize');

                    return new View\Helper\IsAllowed($authorize);
                }
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'isAllowed' => function (AbstractPluginManager $pluginManager) {
                    $serviceLocator = $pluginManager->getServiceLocator();
                    /* @var $authorize \BjyAuthorize\Service\Authorize */
                    $authorize = $serviceLocator->get('Rbac\Service\Authorize');

                    return new Controller\Plugin\IsAllowed($authorize);
                }
            ),
        );
    }

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
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    } 
        
    public function onInstall($sm)
    {
        $rbacInstaller = $sm->get('Rbac\Service\Installer');
        $rbacInstaller->install();
    }
}
