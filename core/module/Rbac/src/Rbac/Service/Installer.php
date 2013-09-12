<?php

namespace Rbac\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Installer implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function install()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $db = $this->serviceManager->get('db');
                
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
                $tmp = call_user_func_array(array($instance, 'getPermissionResources'), array($this->serviceManager));
                $permissionResources[$module] = array_merge($permissionResources[$module], $tmp);
            }            
        }
        
        foreach ($permissionResources as $module=>$value) {
            foreach ($value as $resourceData) {
                $db->query('
                    insert into ' . DB_PREF . 'permission_resources (resource, privelege, name, is_active, module)
                    values (?, ?, ?, ?, ?)', array($resourceData['resource'], $resourceData['privelege'], $resourceData['name'], 1, $module));
            }            
        }
    }
}