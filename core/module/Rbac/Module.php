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
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
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
        $app            = $event->getTarget();
        $serviceManager = $app->getServiceManager();
                
        $guards         = $serviceManager->get('Rbac\Guards');
        foreach ($guards as $guard) {
            $app->getEventManager()->attach($guard);
        }
        
        $config         = $serviceManager->get('config');
        $strategy = $serviceManager->get($config['Rbac']['unauthorized_strategy']);
        $app->getEventManager()->attach($strategy);
        
        $this->setDisplayErrors($serviceManager);
        
        $newUserRolesSetter = $serviceManager->get('Rbac\Service\NewUserRolesSetter');
        $app->getEventManager()->attach($newUserRolesSetter);
        
        $moduleRolesCollector = $serviceManager->get('Rbac\Service\ModulePermissionsCollector');
        $app->getEventManager()->attach($moduleRolesCollector);             
    }
    
    protected function setDisplayErrors($serviceManager)
    {
        $appConfig = $serviceManager->get('ApplicationConfig');
        
        $authService = $serviceManager->get('Rbac\Service\Authorize');
        if ($authService->isAllowed('get_errors') || true == $appConfig['show_errors_to_everybody']) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);         

            $serviceManager->get('viewManager')->getRouteNotFoundStrategy()->setDisplayExceptions(true);
            $serviceManager->get('viewManager')->getRouteNotFoundStrategy()->setDisplayNotFoundReason(true);

            $serviceManager->get('viewManager')->getExceptionStrategy()->setDisplayExceptions(true);

        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);     

            $serviceManager->get('viewManager')->getRouteNotFoundStrategy()->setDisplayExceptions(false);
            $serviceManager->get('viewManager')->getRouteNotFoundStrategy()->setDisplayNotFoundReason(false);

            $serviceManager->get('viewManager')->getExceptionStrategy()->setDisplayExceptions(false);
        }
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
