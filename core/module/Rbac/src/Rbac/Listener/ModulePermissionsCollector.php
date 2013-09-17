<?php

namespace Rbac\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\EventInterface;

class ModulePermissionsCollector implements
    ListenerAggregateInterface,
    ServiceManagerAwareInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('module_installed', array($this, 'onModuleInstalled'));
        $this->listeners[] = $events->attach('module_uninstalled.post', array($this, 'onModuleUninstalled'));
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function onModuleInstalled(EventInterface $event)
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $db = $this->serviceManager->get('db');
        
        $params = $event->getParams();
            
        $module = $params['module'];

        $moduleConfig = $moduleManager->getModuleConfig($module);

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
    }
    
    public function onModuleUninstalled(EventInterface $event)
    {
        $db = $this->serviceManager->get('db');
            
        $params = $event->getParams();

        $module = $params['module'];

        $db->query('delete from ' . DB_PREF . 'permission_resources where module = ?', array($module));
    }
}