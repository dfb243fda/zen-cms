<?php

namespace Pages;

use Zend\Mvc\MvcEvent;

class Module 
{    
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        
        $eventManager = $locator->get('application')->getEventmanager();   
        $eventManager->attach('module_installed', function($e) use ($locator) {
            $params = $e->getParams();
            
            $module = $params['module'];
            
            $moduleConfig = $e->getTarget()->getModuleConfig($module);
            
            if (!empty($moduleConfig['methods'])) {
                $db = $locator->get('db');
                foreach ($moduleConfig['methods'] as $k=>$v) {
                    if (isset($v['type'])) {
                        if ('fe_page' == $v['type']) {
                            $db->query('
                                insert ignore into ' . DB_PREF . 'page_types 
                                    (title, module, method, service) 
                                values (?, ?, ?, ?)', array($v['title'], $module, $k, $v['service']));
                        }
                        
                        if ('fe_content' == $v['type']) {
                            $db->query('
                                insert ignore into ' . DB_PREF . 'page_content_types 
                                    (title, module, method, service) 
                                values (?, ?, ?, ?)', array($v['title'], $module, $k, $v['service']));
                        }
                    }
                }
            }
        });
        
        $eventManager->attach('module_uninstalled', function($e) use ($locator) {
            $params = $e->getParams();
   
            $module = $params['module'];
                        
            $db = $locator->get('db');

            $db->query('
                delete from ' . DB_PREF . 'page_types 
                where module = ?', array($module));
            
            $db->query('
                delete from ' . DB_PREF . 'page_content_types 
                where module = ?', array($module));
        });
    }
    
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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onInstall($sm)
    {
        $moduleManager = $sm->get('moduleManager');
        
        $modules = $moduleManager->getActiveModules();
        
        $pageTypes = array();
        $pageContentTypes = array();
        
        foreach ($modules as $module => $moduleConfig) {
            if (!empty($moduleConfig['methods'])) {
                foreach ($moduleConfig['methods'] as $method=>$methodData) {
                    if (isset($methodData['type'])) {
                        if ('fe_page' == $methodData['type']) {
                            $pageTypes[] = array(
                                'title' => $methodData['title'],
                                'module' => $module,
                                'method' => $method,
                                'service' => $methodData['service'],
                            );
                        } elseif ('fe_content' == $methodData['type']) {
                            $pageContentTypes[] = array(
                                'title' => $methodData['title'],
                                'module' => $module,
                                'method' => $method,
                                'service' => $methodData['service'],
                            );
                        }
                        
                    }
                }
            }
        }
        
        $db = $sm->get('db');
        
        foreach ($pageTypes as $v) {
            $db->query('
                insert ignore into ' . DB_PREF . 'page_types
                    (title, module, method, service)
                values (?, ?, ?, ?)', array($v['title'], $v['module'], $v['method'], $v['service']));
        }
        foreach ($pageContentTypes as $v) {
            $db->query('
                insert ignore into ' . DB_PREF . 'page_content_types
                    (title, module, method, service)
                values (?, ?, ?, ?)', array($v['title'], $v['module'], $v['method'], $v['service']));
        }
        
        $configManager = $sm->get('configManager');
        
        $configManager->set('pages', 'replace_spaces_with', '_');
        
    }
    
    public function getTablesSql()
    {               
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }    
}
