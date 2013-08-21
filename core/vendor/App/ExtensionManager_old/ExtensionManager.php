<?php

namespace App\ExtensionManager;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;

class ExtensionManager
{
    protected $serviceManager;
    
    protected $initialized = false;
        
    protected $db = null;
    
    protected $extensionsTable = 'extensions';
    
    protected $installedExtData = null;
    
    protected $eventManager = null;
    
    
    
    
    
    protected $_request = null;
    
    protected $_response = null;
    
    protected $translator;
    
    protected $_permissionManager = null;
        
    protected $_bootstrapOptions = null;
    
    protected $_extConfig = array();
    
    protected $_defaultBlockWeight = 10;
    
    const CORE_EXT = 'core_ext';
    const APPLICATION_EXT = 'application_ext';
    
    
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        elseif (!is_array($options)) {
            throw new \Exception('Invalid options provided; must be location of config file, a config object, or an array');
        }
        
        $this->setOptions($options);
        
        if (null === $this->serviceManager) {
            throw new \Exception('No serviceManager in options');
        }        
        if (null === $this->db) {
            $this->db = $this->serviceManager->get('db');
        }
        
        $sql = new Sql($this->db);
        
        $select = $sql->select()
                ->from(DB_PREF . $this->extensionsTable, array('extension', 'extension_type', 'is_active', 'is_required'));
        
        $statement = $sql->prepareStatementForSqlObject($select);        
        $resultSet = new ResultSet();
        $sqlRes = $resultSet->initialize($statement->execute())->toArray();
        
        $this->installedExtData = array();
        foreach ($sqlRes as $row) {
            $this->installedExtData[$row['extension']] = $row;
        }
        
    }
    
    protected function init()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            
            $this->translator = $this->serviceManager->get('translator');
            
            $this->eventManager = $this->serviceManager->get('eventManager');
            
/*            
            if (null === $this->serviceManager) {
                throw new \Exception('No serviceManager in options');
            }        
            if (null === $this->_db) {
                $this->_db = $this->serviceManager->get('db');
            }

            if (null === $this->_eventManager) {
                $this->_eventManager = $this->serviceManager->get('eventManager');
            }

            if (null === $this->_request) {
                $this->_request = $this->serviceManager->get('request');
            }

            if (null === $this->_response) {
                $this->_request = $this->serviceManager->get('response');
            }

            if (null === $this->translator) {
                $this->translator = $this->serviceManager->get('translator');
            }

            if (null === $this->_permissionManager) {
                $this->_permissionManager = $this->serviceManager->get('permissionManager');
            }        

            $this->_bootstrapOptions = $this->serviceManager->get('config');
*/            
        }        
    }
    
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
        return $this;
    }
    
    public function setDb($db)
    {
        $this->_db = $db;
        return $this;
    }
        
    public function findExtension($ext_key)
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $pathOptions = $appConfig['path'];

        if (is_dir($pathOptions['core_ext'] . DS . $ext_key)) {
            return $pathOptions['core_ext'] . DS .  $ext_key;
        }
        elseif (is_dir($pathOptions['application_ext'] . DS . $ext_key)) {
            return $pathOptions['application_ext'] . DS . $ext_key;
        }
        else {
            return null;
        }
    }
    
    public function isExtensionExists($ext)
    {
        return (null !== $this->findExtension($ext));
    }
    
    public function isExtensionInstalled($ext)
    {
        return isset($this->installedExtData[$ext]);
    }
    
    public function isExtensionRequired($ext)
    {
        return (isset($this->installedExtData[$ext]) && $this->installedExtData[$ext]['is_required']);
    }
    
    public function isExtensionActive($ext)
    {
        return (isset($this->installedExtData[$ext]) && $this->installedExtData[$ext]['is_active']);
    }
    
    public function getExtPath($extKey)
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $pathOptions = $appConfig['path'];
                
        if (isset($this->installedExtData[$extKey])) {
            if ($this->installedExtData[$extKey]['extension_type'] == self::CORE_EXT) {
                return $pathOptions['core_ext'] . DS . $extKey;
            }
            else {
                return $pathOptions['application_ext'] . DS . $extKey;
            }
        }
        elseif (null !== ($extPath = $this->findExtension($extKey))) {
            return $extPath;
        }
        else {
            throw new \Exception('Does not find extension ' . $extKey);
        }
    }
    
    public function isMethodExists($ext, $module, $method)
    {
        if ($this->isExtensionExists($ext)) {
            $extConfig = $this->getExtConfig($ext);            
            return isset($extConfig['submodules'][$module]['methods'][$method]);
        }
        return false;
    }
    
    public function getExtConfig($ext_key)
    {
        $this->init();
        
        if (!isset($this->_extConfig[$ext_key])) {
            $appConfig = $this->serviceManager->get('ApplicationConfig');
            $pathOptions = $appConfig['path'];
            
            $defaults = array(
                'blocks' => array(),
                'modules' => array(),
                'permissions' => array(),
            );

            $ext_path = $this->getExtPath($ext_key);
   
            $config = $this->serviceManager->get('Config');
            if (isset($config[$ext_key])) {
                $ext_config = $config[$ext_key];
            } else {
                $ext_config = array();
            }
            
            if (false !== strpos($ext_path, $pathOptions['core_ext'])) {
                $ext_config['type'] = self::CORE_EXT;
            } else {
                $ext_config['type'] = self::APPLICATION_EXT;
            }
                        
            foreach ($defaults as $k => $v) {
                if (!isset($ext_config[$k])) {
                    $ext_config[$k] = $v;
                }
            }

            $ext_config['title'] = $this->translator->translateI18n($ext_config['title']);
            $ext_config['description'] = $this->translator->translateI18n($ext_config['description']);

            foreach ($ext_config['modules'] as $modul_key => $modul_data) {
                $ext_config['modules'][$modul_key]['title'] = $this->translator->translateI18n($modul_data['title']);
                $ext_config['modules'][$modul_key]['description'] = $this->translator->translateI18n($modul_data['description']);
                
                foreach ($modul_data['methods'] as $methodKey=>$methodData) {
                    $ext_config['moduls'][$modul_key]['methods'][$methodKey]['title'] = $this->translator->translateI18n($methodData['title']);
                }
            }

            foreach ($ext_config['permissions'] as $perm_key => $perm_value) {
                if (is_array($perm_value)) {
                    $ext_config['permissions'][$perm_key]['title'] = $this->translator->translateI18n($perm_value['title']);
                }
                else {
                    $ext_config['permissions'][$perm_key] = $this->translator->translateI18n($perm_value);
                }
            }

            foreach ($ext_config['blocks'] as $block_key => $block_value) {
                $ext_config['blocks'][$block_key]['caption'] = $this->translator->translateI18n($block_value['caption']);

                if (isset($block_value['settings_sections'])) {
                    foreach ($block_value['settings_sections'] as $settings_section_key => $settings_section_value) {
                        $ext_config['blocks'][$block_key]['settings_sections'][$settings_section_key]['title'] = $this->translator->translateI18n($settings_section_value['title']);
                        $ext_config['blocks'][$block_key]['settings_sections'][$settings_section_key]['description'] = $this->translator->translateI18n($settings_section_value['description']);
                    }
                }
            }


            $ext_config['path'] = $ext_path;
            
            $this->_extConfig[$ext_key] = $ext_config;
        }

        return $this->_extConfig[$ext_key];
    }
    
    public function getExtListFrom($path)
    {
        $list = array();
        if (is_dir($path)) {
            $ext_list = general::getDirs($path);
            if (is_array($ext_list)) {
                foreach ($ext_list as $ext_key) {
                    if (is_file($path . DS . $ext_key . DS . 'config' . DS . 'ext_config.php')) {
                        $list[] = $ext_key;
                    }
                }
            }
        }
        return $list;
    }
    
    public function getAllExtensions($group_by = null)
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $pathOptions = $appConfig['path'];
        
        $extList = $this->getExtListFrom($pathOptions['core_ext']);
        
        $extList = array_merge($extList, $this->getExtListFrom($pathOptions['application_ext']));
        
        $extensionsConfig = array();
        foreach ($extList as $ext) {
            $extensionsConfig[$ext] = $this->getExtConfig($ext);
        }

        if (null === $group_by) {
            return $extensionsConfig;
        }
        else {
            $groupedExtensionsConfig = array();
            foreach ($extensionsConfig as $k => $r) {
                $groupedExtensionsConfig[$r[$group_by]][$k] = $r;
            }
                
            return $groupedExtensionsConfig;
        }
    }
    
    public function getActiveExtensions($group_by = null)
    {        
        $extList = array();
        foreach ($this->installedExtData as $k=>$row) {
            if ($row['is_active']) {
                $extList[] = $k;
            }
        }
                
        $extensionsConfig = array();
        foreach ($extList as $ext) {
            $extensionsConfig[$ext] = $this->getExtConfig($ext);
        }

        if (null === $group_by) {
            return $extensionsConfig;
        }
        else {
            $groupedExtensionsConfig = array();
            foreach ($extensionsConfig as $k => $r) {
                $groupedExtensionsConfig[$r[$group_by]][$k] = $r;
            }
                
            return $groupedExtensionsConfig;
        }
    }
    
    public function getActiveExtensionsPaths()
    {
        $pathes = array();
        foreach ($this->installedExtData as $k => $row) {
            if ($row['is_active']) {
                $pathes[$k] = $this->getExtPath($k);
            }            
        }
        return $pathes;
    }
    
    public function getPageTemplatePath($theme)
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $pathOptions = $appConfig['path'];

        return $pathOptions['page_template'] . DS . $theme;
    }
    
    public function getContentTemplatePath($extKey, $modul = '', $method = '')
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $pathOptions = $appConfig['path'];

        $result = $pathOptions['content_template'] . DS . $extKey;
        
        if ($modul) {
            $result .= DS . $modul;
        }
        if ($method) {
            $result .= DS . $method;
        }
        
        return $result;
    }
    
    public function getExtImgPath($extKey)
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $pathOptions = $appConfig['path'];

        return $pathOptions['public'] . DS . 'img' . DS . $extKey;
    }

    public function getExtJsPath($extKey)
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $pathOptions = $appConfig['path'];

        return $pathOptions['public'] . DS . 'js' . DS . $extKey;
    }

    public function getExtCssPath($extKey)
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $pathOptions = $appConfig['path'];

        return $pathOptions['public']['public'] . DS . 'css' . DS . $extKey;
    }

    public function getExtImgUrl($extKey)
    {
        return ROOT_URL . '/img/' . $extKey;
    }

    public function getExtJsUrl($extKey)
    {
        return ROOT_URL . '/js/' . $extKey;
    }

    public function getExtCssUrl($extKey)
    {
        return ROOT_URL . '/css/' . $extKey;
    }
    
    public function getModulBlocks()
    {
        $extensions = $this->getActiveExtensions();

        $blocks = array();
        foreach ($extensions as $ext_key => $extension) {
            foreach ($extension['blocks'] as $block_key => $block_data) {
                if (isset($blocks[$block_key])) {
                    trigger_error('Идентификатор блока ' . $block_key . ' в расширении ' . $ext_key . ' повторяется.', E_USER_WARNING);
                }
                else {
                    $block_data['items'] = array();
                    if (!isset($block_data['weight'])) {
                        $block_data['weight'] = $this->_defaultBlockWeight;
                    }
                    $blocks[$block_key] = $block_data;
                }
            }
        }

        uasort($blocks, function($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] < $b['weight']) ? -1 : 1;
        });
        
        foreach ($extensions as $ext_key => $extension) {
            foreach ($extension['modules'] as $modul_key => $modul) {
                foreach ($modul['methods'] as $methodKey => $method) {
                    if (isset($method['block'])) {
                        if (isset($blocks[$method['block']])) {
                            $method['modul_key'] = $modul_key;
                            $method['ext_key'] = $ext_key;
                            if ($methodKey != $modul['defaultMethod']) {
                                $method['method_key'] = $methodKey;
                            }
                            $blocks[$method['block']]['items'][] = $method;
                        }
                        else {
                            trigger_error('Не найден блок "' . $method['block'] . '", указанный в методе "' . $method['title'] . '(' . $methodKey . ')"', E_USER_WARNING);
                        }
                    }
                }                
            }
        }
        
        $blocks = $this->eventManager->prepareArgs($blocks);
        $this->eventManager->trigger('get_modul_blocks', $this, $blocks);

        return $blocks;
    }
    
    public function activateExtension($ext)
    {
        $ext = (string)$ext;
        if (isset($this->installedExtData[$ext]) && !$this->installedExtData[$ext]['is_active']) {
            $this->db->update(DB_PREF . $this->_extensionsTable, array(
                'is_active' => 1,
            ), 'extension = ' . $this->db->quote($ext));
            $this->installedExtData[$ext]['is_active'] = true;
            
            $autoloaderLibrary = $this->serviceManager->get('autoloaderLibrary');
            
            $autoloaderLibrary->createAutoLoadFilesMapping();
            
            return true;
        }
        return false;
    }
    
    public function deactivateExtension($ext)
    {
        if (isset($this->installedExtData[$ext]) && $this->installedExtData[$ext]['is_active']) {
            $this->db->update(DB_PREF . $this->_extensionsTable, array(
                'is_active' => 0,
            ), 'extension = ' . $this->db->quote($ext));
            $this->installedExtData[$ext]['is_active'] = false;
            
            $autoloaderLibrary = $this->serviceManager->get('autoloaderLibrary');

            $autoloaderLibrary->createAutoLoadFilesMapping();
                
            return true;
        }
        return false;
    }
    
    public function deleteExtension($ext)
    {
        if (isset($this->installedExtData[$ext])) {
            $this->db->delete(DB_PREF . $this->_extensionsTable, 'extension = ' . $this->db->quote($ext));
            unset($this->installedExtData[$ext]);
            
            $extConfig = $this->getExtConfig($ext);
            
            foreach ($extConfig['moduls'] as $modulName=>$modul) {
                foreach ($modul['methods'] as $methodName=>$method) {
                    if (isset($method['type']) && 'delete' == $method['type']) {
                        $className = $modulName . '_modul';
                        $classOptions = array(
                            'bootstrap' => $this->_bootstrap,
                            'response' => $this->_response,
                            'request' => $this->_request,
                        );
                        $obj = new $className($classOptions);

                        if (!$obj instanceof App_ModulAbstract) {
                            throw new Zend_Exception('Class ' . get_class($obj) . ' does not extend App_ModulAbstract');
                        }

                        $obj->$methodName();                            
                    }
                }
            }
            
            $autoloaderLibrary = $this->serviceManager->get('autoloaderLibrary');

            $autoloaderLibrary->createAutoLoadFilesMapping();
            
            $extPath = $this->getExtPath($ext);
                
            if (is_dir($extPath . '/public')) {
                $dirs = general::getDirs($extPath . '/public');

                if (!empty($dirs)) {
                    foreach ($dirs as $dir) {
                        if (is_dir(PUBLIC_PATH . '/' . $dir . '/' . $ext)) {
                            general::rmdir(PUBLIC_PATH . '/' . $dir . '/' . $ext, true);
                        }
                    }
                }
            }   
            
            return true;
        }
        return false;
    }
    
    public function installExtension($ext, $isRequired)
    {
        if (!isset($this->installedExtData[$ext])) {
            if ($this->isExtensionExists($ext)) {
                $extConfig = $this->getExtConfig($ext);
                
                $this->db->insert(DB_PREF . $this->_extensionsTable, array(
                    'extension' => $ext,
                    'extension_type' => $extConfig['type'],
                    'is_required' => (int)$isRequired,
                    'is_active' => 1,
                ), 'extension = ' . $this->db->quote($ext));
                $this->installedExtData[$ext] = array(
                    'extension' => $ext,
                    'extension_type' => $extConfig['type'],
                    'is_required' => (int)$isRequired,
                    'is_active' => 1,
                );
                
                foreach ($extConfig['moduls'] as $modulName=>$modul) {
                    foreach ($modul['methods'] as $methodName=>$method) {
                        if (isset($method['type']) && 'install' == $method['type']) {
                            $className = $modulName . '_modul';
                            $classOptions = array(
                                'bootstrap' => $this->_bootstrap,
                                'response' => $this->_response,
                                'request' => $this->_request,
                            );
                            $obj = new $className($classOptions);

                            if (!$obj instanceof App_ModulAbstract) {
                                throw new Zend_Exception('Class ' . get_class($obj) . ' does not extend App_ModulAbstract');
                            }

                            $obj->$methodName();                            
                        }
                    }
                }
                
                $extPath = $this->getExtPath($ext);
                                
                $autoloaderLibrary = $this->serviceManager->get('autoloaderLibrary');

                $autoloaderLibrary->createAutoLoadFilesMapping();
                
                if (is_dir($extPath . '/public')) {
                    $dirs = general::getDirs($extPath . '/public');
                    
                    if (!empty($dirs)) {
                        foreach ($dirs as $dir) {
                            if (!is_dir(PUBLIC_PATH . '/' . $dir . '/' . $ext)) {
                                general::mkdir(PUBLIC_PATH . '/' . $dir . '/' . $ext, true);
                            }
                                                        
                            general::recurseCopy($extPath . '/public/' . $dir, PUBLIC_PATH . '/' . $dir . '/' . $ext, true);
                        }
                    }
                }                
                
                return true;
            }
        }
        return false;
    }
}








































