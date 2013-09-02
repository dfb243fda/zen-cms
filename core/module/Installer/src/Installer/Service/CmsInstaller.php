<?php

namespace Installer\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container as SessionContainer;
use Zend\Crypt\Password\Bcrypt;

class CmsInstaller implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    protected $dbAdapter;
    
    public function setDbAdapter($dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        return $this;
    }
    
    public function installCms()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $allModules = $moduleManager->getAllModulesList();
        
        foreach ($allModules as $module=>$path) {
            $priority = 0;
            $moduleConfig = $moduleManager->getModuleConfig($module);
            
            if (isset($moduleConfig['install_priority'])) {
                $priority = $moduleConfig['install_priority'];
            }
            
            $allModules[$module] = array(
                'path' => $path,
                'priority' => $priority,
            );
        }
        
        uasort($allModules, function($a, $b) {
            if ($a['priority'] == $b['priority']) {
                return 0;
            }
            return ($a['priority'] < $b['priority']) ? -1 : 1;
        });
        
                
        $db = $this->dbAdapter;
        
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('Zend\Db\Adapter\Adapter', $db);
                
        foreach ($allModules as $module=>$v) {
            $this->installModule1($module);
        }
        foreach ($allModules as $module=>$v) {
            $this->installModule2($module);
        }
        
        return true;
    }
    
    protected function installModule1($module)
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        if ($moduleManager->isModuleExists($module) && (!$moduleManager->isCmsInstalled() || !$moduleManager->isModuleInstalled($module))) {
            $moduleManager->copyModuleViewToApplication($module);
            $moduleManager->shareModulePublicResources($module);
            
            $sql = $moduleManager->getTablesSql($module);            
            if (null !== $sql) {
                $sqlParser = $this->serviceManager->get('SqlParser');
                $sqlParser->getUpdateSuggestions($sql, true, array('create'));
            }
            return true;
        }
        return false;
    }
    
    protected function installModule2($module)
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        if ($moduleManager->isModuleExists($module) && (!$moduleManager->isCmsInstalled() || !$moduleManager->isModuleInstalled($module))) {
            $modulePath = $moduleManager->getModulePath($module);
            
            $isRequired = 0;
            $priority = 0;
            
            $moduleConfig = $moduleManager->getModuleConfig($module);
            if (isset($moduleConfig['isRequired'])) {
                $isRequired = (int)$moduleConfig['isRequired'];                    
            }
            if (isset($moduleConfig['priority'])) {
                $priority = $moduleConfig['priority'];
            }
            $version = $moduleConfig['version'];
            
            $sql = $moduleManager->getTablesSql($module);            
            if (null !== $sql) {
                $sqlParser = $this->serviceManager->get('SqlParser');
                $sqlParser->getUpdateSuggestions($sql, true, array('insert', 'update', 'foreign'));
            }
            
            $db = $this->serviceManager->get('db');
            $isActive = 1;
            if ('DBSessionStorage' == $module) {
                $isActive = 0;
            }
            $db->query('insert into ' . DB_PREF . 'modules (module, is_active, is_required, version, sorting) values(?, ?, ?, ?, ?)', array($module, $isActive, $isRequired, $version, $priority));
                        
            return true;
        }
        return false;
    }
    
    public function finishInstallCms($demoSite, $email, $password)
    {        
        $installSession = new SessionContainer('installer');
        $translator = $this->serviceManager->get('translator');
        
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $activeModules = $moduleManager->getActiveModules();
        
        foreach ($activeModules as $module=>$moduleConfig) {
            $moduleClass = $module . '\Module';
            $instance = new $moduleClass();
            
            if (method_exists($instance, 'onInstall')) {
                call_user_func_array(array($instance, 'onInstall'), array($this->serviceManager));
            }            
        }      
        
        $this->createUser($email, $password);
        
        $configManager = $this->serviceManager->get('configManager');
        
        $configManager->set('system', 'default_be_theme', 'ChameleonTheme');
        
        $configManager->set('system', 'fe_theme', 'FeTheme');
        
        $configManager->set('system', 'language', $installSession->step1['language']);
        
        $configManager->set('system', 'site_name', $translator->translate('Dynamic config site name'));
        
        $configManager->set('system', 'date_format', 'd-m-Y');
        $configManager->set('system', 'js_date_format', 'dd-mm-yy');
        $configManager->set('system', 'time_format', 'H:i');
        $configManager->set('system', 'js_time_format', 'HH:mm');
                
        $configManager->set('system', 'timezone', date_default_timezone_get());
        
        $configManager->set('system', 'admin_email', $email);
        
        $db = $this->serviceManager->get('db');
        
        $db->query('
            insert into ' . DB_PREF . 'langs
                (prefix, title)
            values (?, ?), (?, ?)
        ', array('ru_RU', 'Русский', 'en_EN', 'English'));
        
        $request = $this->serviceManager->get('request');
        $uri = $request->getUri();     
        $host = $uri->getHost() . $request->getBasePath();
        $db->query('
            insert into ' . DB_PREF . 'domains
                (host, is_default, default_lang_id)
            values
                (?, ?, ?)
        ', array($host, 1, 1));
        
        $fileManager = $this->serviceManager->get('fileManager');
        
        if (!is_dir(APPLICATION_PATH . '/module')) {
            $fileManager->mkdir(APPLICATION_PATH . '/module');
        }               
                        
        if ($demoSite) {
            $this->installDemoSite($demoSite);
        }
        
        unset($installSession->step1);
        unset($installSession->step2);
        unset($installSession->step3);
        unset($installSession->step4);
        unset($installSession->step5);
        unset($installSession->step6);
        unset($installSession->currentStep);
        
        $moduleManager->activateModule('DBSessionStorage');
        $moduleManager->deactivateModule('Installer');
        
        return true;
    }
    
    public function createUser($email, $password)
    {        
        $bcrypt = new Bcrypt;
        $bcrypt->setCost(14);
        $password = $bcrypt->create($password);
        
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        $roleName = $translator->translate('Installer admin role name');
        $displayName = $translator->translate('Installer user display name');
        
        $db->query('insert into ' . DB_PREF . 'roles (name) values (?)', array($roleName));
        
        $roleId = $db->getDriver()->getLastGeneratedValue();
        
        $db->query('insert into ' . DB_PREF . 'role_permissions (resource, privelege, role, is_allowed) values (?, ?, ?, ?)', array('', '', $roleId, 1));
        
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        $adminUserObjectTypeId = $objectTypesCollection->getTypeIdByGuid('user-item');   
        
        $objectId = $objectsCollection->addObject('user-item', $adminUserObjectTypeId);
        
        $db->query('insert into ' . DB_PREF . 'users (username, email, display_name, password, object_id) values (?, ?, ?, ?, ?)', array('admin', $email, $displayName, $password, $objectId));
        
        $userId = $db->getDriver()->getLastGeneratedValue();
        
        $db->query('insert into ' . DB_PREF . 'user_role_linker (user_id, role_id) values (?, ?)', array($userId, $roleId));
    }
    
}