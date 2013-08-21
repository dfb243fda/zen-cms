<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Rbac;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\Mvc\ApplicationInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * BjyAuthorize Module
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
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
        /* @var $app \Zend\Mvc\ApplicationInterface */
        $app            = $event->getTarget();
        /* @var $sm \Zend\ServiceManager\ServiceLocatorInterface */
        $serviceManager = $app->getServiceManager();
        $config         = $serviceManager->get('Rbac\Config');
        $strategy       = $serviceManager->get($config['unauthorized_strategy']);
        $guards         = $serviceManager->get('Rbac\Guards');

        foreach ($guards as $guard) {
            $app->getEventManager()->attach($guard);
        }

        $app->getEventManager()->attach($strategy);
      
 //       $usersService = $serviceManager->get('users_service');
 //       
        $app->getEventManager()->attach('register.post', function($event) use ($serviceManager) {
            $db = $serviceManager->get('db');
            
            $params = $event->getParams();
            
            $userId = $params['userId'];
            
            $configManager = $serviceManager->get('configManager');
            
            $roles = $configManager->get('users', 'new_user_roles');
         
            if (!empty($roles)) {
                foreach ($roles as $roleId) {
                    $db->query('insert into ' . DB_PREF . 'user_role_linker (user_id, role_id) values (?, ?)', array($userId, $roleId));
                } 
            }
        });
        
        $app->getEventManager()->attach('module_installed', function($e) use ($serviceManager) {
            $params = $e->getParams();
            
            $module = $params['module'];
            
            $moduleManager = $serviceManager->get('moduleManager');
        
            $moduleConfig = $moduleManager->getModuleConfig($module);
               
            $db = $serviceManager->get('db');
            
            $permissionResources = array();
            
            $moduleClass = $module . '\Module';
            $instance = new $moduleClass();

            if (isset($moduleConfig['permission_resources'])) {
                $permissionResources[$module] = $moduleConfig['permission_resources'];
            }

            if (method_exists($instance, 'getPermissionResources')) {
                $tmp = call_user_func_array(array($instance, 'getPermissionResources'), array($sm));
                $permissionResources[$module] = array_merge($permissionResources[$module], $tmp);
            }      

            foreach ($permissionResources as $module=>$value) {
                foreach ($value as $resourceData) {
                    $db->query('
                        insert into ' . DB_PREF . 'permission_resources (resource, privelege, name, is_active, module)
                        values (?, ?, ?, ?, ?)', array($resourceData['resource'], $resourceData['privelege'], $resourceData['name'], 1, $module));
                }            
            }
        });
        
        $app->getEventManager()->attach('module_uninstalled.post', function($event) use ($serviceManager) {
            $db = $serviceManager->get('db');
            
            $params = $event->getParams();
            
            $module = $params['module'];
            
            $db->query('delete from ' . DB_PREF . 'permission_resources where module = ?', array($module));
        });
        
    }
    
    public function getDynamicConfig($sm)
    {
        $db = $sm->get('db');
        
        $sqlRes = $db->query('select id, name from ' . DB_PREF . 'roles', array())->toArray();
        
        $roles = array();
        foreach ($sqlRes as $row) {
            $roles[$row['id']] = $row['name'];
        }
        
        return array(
            'form' => array(
                'registration' => array(
                    'fieldsets' => array(
                        'users' => array(
                            'spec' => array(
                                'name' => 'users',
                                'elements' => array(
                                    'new_user_roles' => array(
                                        'spec' => array(
                                            'type' => 'select',
                                            'name' => 'new_user_roles',
                                            'options' => array(
                                                'label' => 'i18n::Dynamic config new user roles',
                                                'description' => 'i18n::Dynamic config new user roles description',
                                                'value_options' => $roles,
                                            ),
                                            'attributes' => array(
                                                'multiple' => true,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
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
        $moduleManager = $sm->get('moduleManager');
                
        $permissionResources = array();
        
        $modules = $moduleManager->getActiveModules();
        
        foreach ($modules as $module=>$moduleConfig) {
            $moduleClass = $module . '\Module';
            $instance = new $moduleClass();
            
            if (isset($moduleConfig['permission_resources'])) {
                $permissionResources[$module] = $moduleConfig['permission_resources'];
            } else {
                $permissionResources[$module] = array();
            }
            
            if (method_exists($instance, 'getPermissionResources')) {
                $tmp = call_user_func_array(array($instance, 'getPermissionResources'), array($sm));
                $permissionResources[$module] = array_merge($permissionResources[$module], $tmp);
            }            
        }
        
        $db = $sm->get('db');
        foreach ($permissionResources as $module=>$value) {
            foreach ($value as $resourceData) {
                $db->query('
                    insert into ' . DB_PREF . 'permission_resources (resource, privelege, name, is_active, module)
                    values (?, ?, ?, ?, ?)', array($resourceData['resource'], $resourceData['privelege'], $resourceData['name'], 1, $module));
            }            
        }
    }
}
