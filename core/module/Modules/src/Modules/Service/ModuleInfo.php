<?php

namespace Modules\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class ModuleInfo implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    const STATUS_OK = 'ok';
    const STATUS_REMOVED = 'removed';
    const STATUS_MODIFIED = 'modified';
    
    protected $states = array(
        self::STATUS_OK       => 'Modules difference state ok',
        self::STATUS_REMOVED  => 'Modules difference state removed',
        self::STATUS_MODIFIED => 'Modules difference state modified',
    );
    
    protected $module;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }
    
    public function getModuleInfo()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $translator = $this->serviceManager->get('translator');
        
        $moduleConfig = $moduleManager->getModuleConfig($this->module);
        
        $moduleConfig['title'] = $translator->translateI18n($moduleConfig['title']);
        $moduleConfig['description'] = $translator->translateI18n($moduleConfig['description']);
        
        if (!empty($moduleConfig['methods'])) {
            foreach ($moduleConfig['methods'] as $k=>$v) {
                $moduleConfig['methods'][$k]['title'] = $translator->translateI18n($v['title']);
                $moduleConfig['methods'][$k]['description'] = $translator->translateI18n($v['description']);
            }
        }
        
        return $moduleConfig;
    }
    
    public function getDbDifference()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $sql = $moduleManager->getTablesSql($this->module);
        
        $sqlParser = $this->serviceManager->get('SqlParser');
        
        return $sqlParser->getUpdateSuggestions($sql);                    
    }
    
    public function getFilesDifference()
    {
        $module = $this->module;
        
        $config = $this->serviceManager->get('ApplicationConfig');  
        
        $moduleManager = $this->serviceManager->get('moduleManager');
        $fileManager = $this->serviceManager->get('fileManager');
        
        $modulePath = $moduleManager->getModulePath($module);
        
        $translator = $this->serviceManager->get('translator');
        
        $basePaths = array(
            'view' => array(
                'title' => $translator->translate('Modules info views'),
                'path' => $config['path']['application_view'],
            ),
            'public' => array(
                'title' => $translator->translate('Modules info public'),
                'path' => $config['path']['public'],
            ),
        );
        
        $filesDifference = array();
        
        foreach ($basePaths as $key=>$pathData) {
            $filesDifference[$key] = array(
                'title' => $pathData['title'],
                'items' => array(),
            );
            if (is_dir($modulePath . '/' . $key)) {
                $dirs = $fileManager->getDirs($modulePath . '/' . $key);
                foreach ($dirs as $dir) {
                    $files = $fileManager->getAllFilesAndFoldersInPath(array(), $modulePath . '/' . $key . '/' . $dir);
                    foreach ($files as $pathMd5 => $file) {
                        $segmentPath = substr($file, strlen($modulePath . '/' . $key . '/' . $dir));

                        $sourceFile = $file;
                        $targetFile = $pathData['path'] . '/' . $dir . '/' . $module . $segmentPath;

                        if (is_file($targetFile)) {
                            $sourceMd5 = md5_file($sourceFile);
                            $targetMd5 = md5_file($targetFile);

                            if ($sourceMd5 == $targetMd5) {
                                $status = self::STATUS_OK;
                            } else {
                                $status = self::STATUS_MODIFIED;
                            }                        
                        } else {
                            $status = self::STATUS_REMOVED;
                        }

                        $sourceFile = str_replace(array('\\', '/'), DS, $sourceFile);
                        $targetFile = str_replace(array('\\', '/'), DS, $targetFile);
                        
                        if (self::STATUS_OK != $status) {
                            $filesDifference[$key]['items'][] = array(
                                'pathMd5'    => $pathMd5,
                                'sourceFile' => $sourceFile,
                                'targetFile' => $targetFile,
                                'status'     => $status,
                                'statusMsg'  => $translator->translate($this->states[$status]),
                            );
                        }                    
                    }
                }            
            }
        }
        
        foreach ($filesDifference as $k=>$v) {
            if (empty($v['items'])) {
                unset($filesDifference[$k]);
            }
        }
          
        return $filesDifference;
    }
    
    public function updateDbDifference($post)
    {
        $dbDifference = $this->getDbDifference();
        $db = $this->serviceManager->get('db');
        
        if (isset($post['query']) && is_array($post['query'])) {
            foreach ($dbDifference as $k => $v) {
                foreach ($v as $k2 => $v2) {
                    if (in_array($k2, $post['query'])) {
                        if (is_array($v2)) {
                            $v2 = $v2['query'];
                        }
                        $db->query($v2, array());
                    }                    
                }
            }
        }        
    }
    
    public function updateFilesDifference($post)
    {
        $filesDifference = $this->getFilesDifference();
        $fileManager = $this->serviceManager->get('fileManager');
        
        if (isset($post['file']) && is_array($post['file'])) {
            foreach ($filesDifference as $k => $v) {
                foreach ($v['items'] as $item) {                    
                    if (in_array($item['pathMd5'], $post['file'])) {                        
                        if (!is_dir(dirname($item['targetFile']))) {
                            $fileManager->mkdir(dirname($item['targetFile']), true);
                        }          
                        copy($item['sourceFile'], $item['targetFile']);
                    }
                }
            }
        }        
    }
    
    
}