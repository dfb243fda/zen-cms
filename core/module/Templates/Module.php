<?php

namespace Templates;

use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        
        $eventManager = $locator->get('application')->getEventmanager();   
        $eventManager->attach('module_uninstalled', function($e) use ($locator) {
            $params = $e->getParams();
            
            $module = $params['module'];
            
            $db = $locator->get('db');
            
            $sqlRes = $db->query('select id from ' . DB_PREF . 'templates where module = ?', array($module))->toArray();
            
            foreach ($sqlRes as $row) {
                $db->query('delete from ' . DB_PREF . 'template_markers where template_id = ?', array($row['id']));
                $db->query('delete from ' . DB_PREF . 'templates where id = ?', array($row['id']));
            }
        });
        
        $eventManager->attach('module_installed', function($e) use ($locator) {
            $params = $e->getParams();
            
            $module = $params['module'];
            
            $moduleManager = $locator->get('moduleManager');
        
            $moduleConfig = $moduleManager->getModuleConfig($module);
               
            $db = $locator->get('db');
            
            if (isset($moduleConfig['default_templates'])) {                
                foreach ($moduleConfig['default_templates'] as $template) { 
                    if (isset($template['is_default'])) {
                        $isDefault = 1;
                    } else {
                        $isDefault = 0;
                    }
                    if (isset($template['method'])) {
                        $method = $template['method'];
                    } else {
                        $method = '';
                    }

                    $db->query('
                        insert ignore into ' . DB_PREF . 'templates
                            (name, title, type, module, method, is_default)
                        values (?, ?, ?, ?, ?, ?)', array($template['name'], $template['title'], $template['type'], $module, $method, $isDefault));

                    if (!empty($template['markers'])) {
                        $templateId = $db->getDriver()->getLastGeneratedValue();

                        foreach ($template['markers'] as $marker) {
                            $db->query('
                                insert into ' . DB_PREF . 'template_markers
                                    (name, title, template_id)
                                values (?, ?, ?)', array($marker['name'], $marker['title'], $templateId));
                        }                
                    }        
                }
            }
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
        
        $db = $sm->get('db');
        
        foreach ($modules as $moduleKey => $moduleConfig) {
            if (isset($moduleConfig['default_templates'])) {                
                foreach ($moduleConfig['default_templates'] as $template) {
                    if (isset($template['is_default'])) {
                        $isDefault = 1;
                    } else {
                        $isDefault = 0;
                    }
                    if (isset($template['method'])) {
                        $method = $template['method'];
                    } else {
                        $method = '';
                    }

                    $db->query('
                        insert ignore into ' . DB_PREF . 'templates
                            (name, title, type, module, method, is_default)
                        values (?, ?, ?, ?, ?, ?)', array($template['name'], $template['title'], $template['type'], $moduleKey, $method, $isDefault));

                    if (!empty($template['markers'])) {
                        $templateId = $db->getDriver()->getLastGeneratedValue();

                        foreach ($template['markers'] as $marker) {
                            $db->query('
                                insert into ' . DB_PREF . 'template_markers
                                    (name, title, template_id)
                                values (?, ?, ?)', array($marker['name'], $marker['title'], $templateId));
                        }                
                    }
                }
            }
        }
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    } 
}