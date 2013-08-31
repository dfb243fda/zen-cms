<?php

namespace App\ModuleManager;

use Zend\ModuleManager\ModuleManager as StandardModuleManager;

use Zend\EventManager\EventManagerInterface;

class ModuleManager extends StandardModuleManager
{
    protected $serviceManager;
    
    protected $installedModulesData = array();
        
    protected $modulesTable = 'modules';
        
    protected $isCmsInstalled;
    
    protected $modulesConfig = array();
        
    public function __construct(EventManagerInterface $eventManager = null)
    {
        if ($eventManager instanceof EventManagerInterface) {
            $this->setEventManager($eventManager);
        }
    }
        
    public function initModules()
    {
        $this->serviceManager = $serviceManager = $this->getEvent()->getParam('ServiceManager');
        $applicationConfig = $serviceManager->get('ApplicationConfig');
        
        if (isset($applicationConfig['isInstalled']) && $applicationConfig['isInstalled']) {
            $this->isCmsInstalled = true;
            
            $db = $this->serviceManager->get('db');
                
            $sqlRes = $db->query('select * 
                from ' . $applicationConfig['dbPref'] . $this->modulesTable . '
                order by sorting', array())->toArray();

            $this->installedModulesData = array();
            $modules = array();
            foreach ($sqlRes as $row) {
                $this->installedModulesData[$row['module']] = $row;
                if ($row['is_active']) {
                    $modules[] = $row['module'];
                }
            }
        } else {
            $this->isCmsInstalled = false;
            $modules = array(
                'Installer'
            );
        }
        
        
        $this->setModules($modules);
        return $this;
    }
    
    public function isModuleInstalled($module)
    {
        return isset($this->installedModulesData[$module]);
    }
    
    public function isModuleRequired($module)
    {
        return (isset($this->installedModulesData[$module]) && $this->installedModulesData[$module]['is_required']);
    }
    
    public function isModuleActive($module)
    {
        return (isset($this->installedModulesData[$module]) && $this->installedModulesData[$module]['is_active']);
    }
    
    public function getModuleConfig($module)
    {
        if (isset($this->modulesConfig[$module])) {
            return $this->modulesConfig[$module];
        }
        
        $moduleConfig = array();
        if ($this->isModuleActive($module)) {
            $config = $this->serviceManager->get('config');   
            
            if (isset($config[$module])) {
                $moduleConfig = $config[$module];
                return $moduleConfig;
            }
        } else {
            $className = $module . '\Module';
            if (class_exists($className) && method_exists($module . '\Module', 'getConfig')) {
                $instance = new $className;
                $config = $instance->getConfig();
                
                if (isset($config[$module])) {
                    $moduleConfig = $config[$module];
                }
            }
        }   
        
        $this->modulesConfig[$module] = $moduleConfig;
        return $moduleConfig;
    }
    
    public function isMethodExists($module, $method)
    {
        if ($this->isModuleInstalled($module)) {
            $moduleConfig = $this->getModuleConfig($module);            
            return isset($moduleConfig['methods'][$method]);
        }
        return false;
    }
    
    public function getActiveModules($groupBy = null)
    {        
        $modulesList = array();
        foreach ($this->installedModulesData as $k=>$row) {
            if ($row['is_active']) {
                $modulesList[] = $k;
            }
        }
                
        $modulesConfig = array();
        foreach ($modulesList as $module) {
            $modulesConfig[$module] = $this->getModuleConfig($module);
        }

        if (null === $groupBy) {
            return $modulesConfig;
        }
        else {
            $groupedModulesConfig = array();
            foreach ($modulesConfig as $k => $r) {
                $groupedModulesConfig[$r[$groupBy]][$k] = $r;
            }
                
            return $groupedModulesConfig;
        }
    }
    
    public function isCmsInstalled()
    {
        return $this->isCmsInstalled;
    }
    
    public function updateModule($module)
    {
        if ($this->isModuleInstalled($module)) {            
            $isRequired = 0;
            $priority = 0;
            $moduleConfig = $this->getModuleConfig($module);
            
            $version = $moduleConfig['version'];
            
            if (isset($moduleConfig['isRequired'])) {
                $isRequired = (int)$moduleConfig['isRequired'];                    
            }
            if (isset($moduleConfig['priority'])) {
                $priority = $moduleConfig['priority'];
            }
            
            $eventManager = $this->serviceManager->get('application')->getEventmanager();   
            $eventManager->trigger('module_installed', $this, array('module' => $module));
            
            $this->copyModuleViewToApplication($module);
            $this->shareModulePublicResources($module);
            
            $moduleClass = $module . '\Module';
            $obj = new $moduleClass();           
            if (method_exists($obj, 'getTablesSql')) {
                $tablesSql = $obj->getTablesSql($this->serviceManager);
                $sqlParser = $this->serviceManager->get('SqlParser');
                $sqlParser->getUpdateSuggestions($tablesSql, true);
            }
            if (method_exists($obj, 'onInstall')) {
                call_user_func_array(array($obj, 'onInstall'), array($this->serviceManager));
            }
            
            $db = $this->serviceManager->get('db');
            $db->query('update ' . DB_PREF . $this->modulesTable . ' set is_required = ?, version = ?, sorting = ? where module = ?', array($isRequired, $version, $priority, $module));
            
            $eventManager->trigger('module_installed.post', $this, array('module' => $module));
            
            return true;
        }
        return false;
    }
    
    public function installModule($module)
    {
        if ($this->isModuleExists($module) && (!$this->isCmsInstalled || !$this->isModuleInstalled($module))) {            
            $isRequired = 0;
            $priority = 0;
            $moduleConfig = $this->getModuleConfig($module);
            
            $version = $moduleConfig['version'];
            
            if (isset($moduleConfig['isRequired'])) {
                $isRequired = (int)$moduleConfig['isRequired'];                    
            }
            if (isset($moduleConfig['priority'])) {
                $priority = $moduleConfig['priority'];
            }
            
            $eventManager = $this->serviceManager->get('application')->getEventmanager();   
            $eventManager->trigger('module_installed', $this, array('module' => $module));
            
            $this->copyModuleViewToApplication($module);
            $this->shareModulePublicResources($module);
            
            $moduleClass = $module . '\Module';
            $obj = new $moduleClass();           
            if (method_exists($obj, 'getTablesSql')) {
                $tablesSql = $obj->getTablesSql($this->serviceManager);
                $sqlParser = $this->serviceManager->get('SqlParser');
                $sqlParser->getUpdateSuggestions($tablesSql, true);
            }
            if (method_exists($obj, 'onInstall')) {
                call_user_func_array(array($obj, 'onInstall'), array($this->serviceManager));
            }
            
            $db = $this->serviceManager->get('db');
            $db->query('insert into ' . DB_PREF . $this->modulesTable . ' (module, is_active, is_required, version, sorting) values(?, ?, ?, ?, ?)', array($module, 1, $isRequired, $version, $priority));
            
            $eventManager->trigger('module_installed.post', $this, array('module' => $module));
            
            return true;
        }
        return false;
    }
    
    public function getTablesSql($module)
    {
        $sql = null;
        if ($this->isModuleExists($module)) {
            $moduleClass = $module . '\Module';
            $obj = new $moduleClass();
            if (method_exists($obj, 'getTablesSql')) {
                $sql = $obj->getTablesSql($this->serviceManager);
            }            
        }
        return $sql;
    }
    
    public function copyModuleViewToApplication($module)
    {
        $modulePath = $this->getModulePath($module);
        
        if (is_dir($modulePath . '/view')) {
            $fileManager = $this->serviceManager->get('fileManager');
            $config = $this->serviceManager->get('ApplicationConfig');
            
            $dirs = $fileManager->getDirs($modulePath . '/view');
            foreach ($dirs as $dir) {
                if (!is_dir($config['path']['application_view'] . '/' . $dir . '/' . $module)) {
                    $fileManager->mkdir($config['path']['application_view'] . '/' . $dir . '/' . $module, true);
                }
                $fileManager->recurseCopy($modulePath . '/view/' . $dir, $config['path']['application_view'] . '/' . $dir . '/' . $module);
            }
        }        
    }
    
    public function removeModuleView($module)
    {
        $fileManager = $this->serviceManager->get('fileManager');
        $config = $this->serviceManager->get('ApplicationConfig');
        
        $dirs = $fileManager->getDirs($config['path']['application_view']);
        
        foreach ($dirs as $dir) {
            if (is_dir($config['path']['application_view'] . '/' . $dir . '/' . $module)) {
                $fileManager->rmdir($config['path']['application_view'] . '/' . $dir . '/' . $module, true);
            }
        }
    }
    
    public function shareModulePublicResources($module)
    {
        $modulePath = $this->getModulePath($module);
        
        if (is_dir($modulePath . '/public')) {
            $fileManager = $this->serviceManager->get('fileManager');
            $config = $this->serviceManager->get('ApplicationConfig');
            
            $dirs = $fileManager->getDirs($modulePath . '/public');
            foreach ($dirs as $dir) {
                if (!is_dir($config['path']['public'] . '/' . $dir . '/' . $module)) {
                    $fileManager->mkdir($config['path']['public'] . '/' . $dir . '/' . $module, true);
                }
                $fileManager->recurseCopy($modulePath . '/public/' . $dir, $config['path']['public'] . '/' . $dir . '/' . $module);
            }
        }  
    }
    
    public function removeModulePublicResources($module)
    {
        $fileManager = $this->serviceManager->get('fileManager');
        $config = $this->serviceManager->get('ApplicationConfig');
        
        $dirs = $fileManager->getDirs($config['path']['public']);
        
        foreach ($dirs as $dir) {
            if (is_dir($config['path']['public'] . '/' . $dir . '/' . $module)) {
                $fileManager->rmdir($config['path']['public'] . '/' . $dir . '/' . $module, true);
            }
        }
    }
    
    public function getModulePath($module)
    {
        $config = $this->serviceManager->get('ApplicationConfig');
        
        $modulePaths = $config['module_listener_options']['module_paths'];
        
        foreach ($modulePaths as $path) {
            if (is_dir($path . '/' . $module) && file_exists($path . '/' . $module . '/Module.php')) {
                return $path . '/' . $module;
            }
        }
        return null;
    }
    
    public function uninstallModule($module)
    {
        if ($this->isModuleInstalled($module)) {
            $eventManager = $this->serviceManager->get('application')->getEventmanager();   
            $eventManager->trigger('module_uninstalled', $this, array('module' => $module));
            
            $this->removeModuleView($module);
            $this->removeModulePublicResources($module);
            
            $moduleClass = $module . '\Module';
            $obj = new $moduleClass();
            if (method_exists($obj, 'onUninstall')) {
                call_user_func_array(array($obj, 'onUninstall'), array($this->serviceManager));
            }
            
            $db = $this->serviceManager->get('db');
            $db->query('delete from ' . DB_PREF . $this->modulesTable . ' where module = ?', array($module));
            
            $eventManager->trigger('module_uninstalled.post', $this, array('module' => $module));
            
            return true;
        }
        return false;
    }
    
    public function activateModule($module)
    {
        if ($this->isModuleInstalled($module) && !$this->isModuleActive($module)) {
            $db = $this->serviceManager->get('db');
            $db->query('update ' . DB_PREF . $this->modulesTable . ' set is_active = 1 where module = ?', array($module));
            $moduleClass = $module . '\Module';
            $obj = new $moduleClass();
            if (method_exists($obj, 'onActivate')) {
                call_user_func_array(array($obj, 'onActivate'), array($this->serviceManager));
            }
            return true;
        }
        return false;
    }
    
    public function deactivateModule($module)
    {
        if ($this->isModuleActive($module)) {
            $db = $this->serviceManager->get('db');
            $db->query('update ' . DB_PREF . $this->modulesTable . ' set is_active = 0 where module = ?', array($module));
            $moduleClass = $module . '\Module';
            $obj = new $moduleClass();
            if (method_exists($obj, 'onDeactivate')) {
                call_user_func_array(array($obj, 'onDeactivate'), array($this->serviceManager));
            }
            return true;
        }
        return false;
    }
    
    public function isModuleExists($module)
    {
        $modules = $this->getAllModulesList();
        return isset($modules[$module]);
    }
    
    public function getAllModulesList()
    {
        $config = $this->serviceManager->get('ApplicationConfig');
        
        $modulePaths = $config['module_listener_options']['module_paths'];
        
        $fileManager = $this->serviceManager->get('FileManager');
        
        $modules = array();
        foreach ($modulePaths as $path) {
            if (is_dir($path)) {
                $dirs = $fileManager->getDirs($path);
                foreach ($dirs as $dirName) {
                    if (file_exists($path . '/' . $dirName . '/Module.php')) {
                        $modules[$dirName] = $path . '/' . $dirName;
                    }
                }
            }            
        }
        return $modules;
    }   
    
}