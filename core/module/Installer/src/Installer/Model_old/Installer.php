<?php

namespace Installer\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Session\Container as SessionContainer;
use Zend\Crypt\Password\Bcrypt;

class Installer implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $initialized = false;
    
    protected $translator;
    
    protected function init()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            
            $this->translator = $this->serviceManager->get('translator');
        }
    }
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function copyInstallerResources()
    {
        $fileManager = $this->serviceManager->get('fileManager');
        
        $config = $this->serviceManager->get('applicationConfig');
        
        $dirs = $fileManager->getDirs($config['path']['core_public_resources']);
        foreach ($dirs as $dir) {
            if (!is_dir($config['path']['public'] . '/' . $dir . '/core')) {
                $fileManager->mkdir($config['path']['public'] . '/' . $dir . '/core', true);
            }
            $fileManager->recurseCopy($config['path']['core_public_resources'] . '/' . $dir, $config['path']['public'] . '/' . $dir . '/core');
        }
        
        $dirs = $fileManager->getDirs($config['path']['core_view']);
        foreach ($dirs as $dir) {
            if (!is_dir($config['path']['application_view'] . '/' . $dir . '/core')) {
                $fileManager->mkdir($config['path']['application_view'] . '/' . $dir . '/core', true);
            }
            $fileManager->recurseCopy($config['path']['core_view'] . '/' . $dir, $config['path']['application_view'] . '/' . $dir . '/core');
        }        
          
        if (!is_dir($config['path']['application_temp'])) {
            $fileManager->mkdir($config['path']['application_temp'], true);
        }
        if (!is_dir($config['path']['application_files'])) {
            $fileManager->mkdir($config['path']['application_files'], true);
        }
        if (!is_dir($config['path']['public_uploads'])) {
            $fileManager->mkdir($config['path']['public_uploads'], true);
        }
        if (!is_dir($config['path']['public_temp'])) {
            $fileManager->mkdir($config['path']['public_temp'], true);
        }
        if (!is_dir($config['path']['public_files'])) {
            $fileManager->mkdir($config['path']['public_files'], true);
        }
        
        
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $moduleManager->copyModuleViewToApplication('Installer');
        
        $moduleManager->shareModulePublicResources('Installer');
    }
    
    public function getDbFormDefaultValues()
    {
        return array(
            'dbname' => 'zen_cms',
            'dbuser' => 'username',
            'dbpass' => 'password',
            'dbaddr' => 'localhost',
            'dbpref' => 'zen_',
        );
    }
    
    public function getDbFormConfig()
    {
        $this->init();
        
        return array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'dbname',
                        'options' => array(
                            'label' => $this->translator->translate('Installer database name'),
                            'description' => $this->translator->translate('Installer database name description'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'dbuser',
                        'options' => array(
                            'label' => $this->translator->translate('Installer database user'),
                            'description' => $this->translator->translate('Installer database user description'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'dbpass',
                        'options' => array(
                            'label' => $this->translator->translate('Installer database password'),
                            'description' => $this->translator->translate('Installer database password description'),
                        ),
                        'attributes' => array(
                            'autocomplete' => false,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'dbaddr',
                        'options' => array(
                            'label' => $this->translator->translate('Installer database server address'),
                            'description' => $this->translator->translate('Installer database server address description'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'dbpref',
                        'options' => array(
                            'label' => $this->translator->translate('Installer tables prefix'),
                            'description' => $this->translator->translate('Installer tables prefix description'),
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'dbname' => array(
                    'required' => true,
                ),
                'dbuser' => array(
                    'required' => true,
                ),
                'dbpass' => array(
                    'required' => true,
                ),
                'dbaddr' => array(
                    'required' => true,
                ),
            ),
        );
    }
    
    public function getLanguageFormConfig()
    {
        $this->init();
        
        return array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'language',
                        'type' => 'select',
                        'options' => array(
                            'label' => $this->translator->translate('Installer language'),
                            'description' => $this->translator->translate('Installer language description'),
                            'value_options' => array(
                                'ru_RU' => 'Русский',
                                'en_EN' => 'Английский',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
    
    public function getLanguageFormDefaultValues()
    {
        return array(
            'language' => 'ru_RU',
        );
    }
    
    public function getCheckServerResult()
    {
        $this->init();
        
        $result = array(
            'items' => array(),
            'success' => true,
        );
        
        $success = (version_compare(PHP_VERSION, '5.3.3') >= 0);
        $result['success'] = $result['success'] && $success;        
        $result['items'][] = array(
            'title' => $this->translator->translate('Installer php version >= 5.3.3'),
            'success' => $success,
        );
        
        
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            $success = in_array('mod_rewrite', $modules);
        } else {
            $success =  getenv('HTTP_MOD_REWRITE')=='On' ? true : false ;
        }
        $result['success'] = $result['success'] && $success;        
        $result['items'][] = array(
            'title' => $this->translator->translate('Installer mod_rewrite is on'),
            'success' => $success,
        );
        
        $success = false;
        if (extension_loaded('gd') && function_exists('gd_info')) {
            $success = true;
        }
        $result['success'] = $result['success'] && $success;        
        $result['items'][] = array(
            'title' => $this->translator->translate('Installer gd ext is installed'),
            'success' => $success,
        );
        
        $success = false;
        if (extension_loaded('intl') && class_exists('NumberFormatter', false)) {
            $success = true;
        }
        $result['success'] = $result['success'] && $success;
        $result['items'][] = array(
            'title' => $this->translator->translate('Installer intl ext is installed'),
            'success' => $success,
        );
        
        
        return $result;
    }
    
    public function installCms()
    {
        $this->init();
        
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
        
        $installSession = new SessionContainer('installer');
        
        $config = $this->serviceManager->get('ApplicationConfig');
        $config['db']['dsn'] = 'mysql:dbname=' . $installSession->step2['dbname'] . ';host=' . $installSession->step2['dbaddr'];
        $config['db']['username'] = $installSession->step2['dbuser'];
        $config['db']['password'] = $installSession->step2['dbpass'];

        define('DB_PREF', $installSession->step2['dbpref']);
        
        $db = new Adapter($config['db']);
        
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
    
    public function finishInstallCms($demoSite, $email, $password)
    {
        $this->init();
        
        $installSession = new SessionContainer('installer');
        
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
        
        $configManager->set('system', 'site_name', $this->translator->translate('Dynamic config site name'));
        
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
    
    public function getDemoSites()
    {
        $this->init();
        
        $config = $this->serviceManager->get('config');
        
        if (isset($config['Installer']['demoSites'])) {
            $demoSites = $config['Installer']['demoSites'];
            
            foreach ($demoSites as $k=>$v) {
                $demoSites[$k]['title'] = $this->translator->translateI18n($v['title']);
            }
        } else {
            $demoSites = array();
        }
        
        return $demoSites;
    }
    
    public function getAccessFormConfig()
    {
        $this->init();
        
        return array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'email',
                        'options' => array(
                            'label' => $this->translator->translate('Installer email'),
                            'description' => $this->translator->translate('Installer email description'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'password',
                        'options' => array(
                            'label' => $this->translator->translate('Installer password'),
                            'description' => $this->translator->translate('Installer password description'),
                        ),
                        'attributes' => array(
                            'autocomplete' => false,
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'email' => array(
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),  
                        array('name' => 'StringToLower'),
                    ),
                    'validators' => array(
                        array('name' => 'EmailAddress'),
                    ),
                ),
                'password' => array(
                    'required' => true,
                    'filters'    => array(
                        array('name' => 'StringTrim')
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => array(
                                'min' => 6,
                            ),
                        ),
                    ),
                )
            ),
        );
    }
    
    public function createUser($email, $password)
    {
        $this->init();
        
        $bcrypt = new Bcrypt;
        $bcrypt->setCost(14);
        $password = $bcrypt->create($password);
        
        $db = $this->serviceManager->get('db');
        
        $roleName = $this->translator->translate('Installer admin role name');
        $displayName = $this->translator->translate('Installer user display name');
        
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
    
    public function createConfigFile()
    {
        $fileManager = $this->serviceManager->get('fileManager');
        
        if (!is_dir(APPLICATION_PATH . '/config/autoload')) {
            $fileManager->mkdir(APPLICATION_PATH . '/config/autoload', true);
        }        
        
        $configFile = APPLICATION_PATH . '/config/application.config.php';
        
        $installSession = new SessionContainer('installer');
        
        $dsn = 'mysql:dbname=' . $installSession->step2['dbname'] . ';host=' . $installSession->step2['dbaddr'];
        $userName = $installSession->step2['dbuser'];
        $pass = $installSession->step2['dbpass'];
        $dbPref = $installSession->step2['dbpref'];
        
        $phpCode = <<<PHP
<?php

return function(&\$config) {
    \$config['isInstalled'] = true;
    \$config['dbPref'] = '{$dbPref}';
    \$config['db'] = array(
        'driver' => 'Pdo',
        'dsn' => '{$dsn}',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
        ),
        'username' => '{$userName}',
        'password' => '{$pass}',
        'profiler' => true,    
    );
};

PHP;
        
        file_put_contents($configFile, $phpCode);
        
        $configFile = APPLICATION_PATH . '/config/autoload/global.php';
        
        $phpCode = <<<PHP
<?php

return array(    

);
   
PHP;
        
        file_put_contents($configFile, $phpCode);
        
    }
    
    public function installDemoSite($site)
    {
        $sites = $this->getDemoSites();
        
        if ($site && isset($sites[$site])) {
            $instance = $this->serviceManager->get($sites[$site]['service']);
            
            $instance->createDemoSite();
        }
    }
    
}