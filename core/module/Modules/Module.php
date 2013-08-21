<?php

namespace Modules;


class Module 
{    
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
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }
    
    public function getPermissionResources($sm)
    {
        $resources = array();
        
        $moduleManager = $sm->get('moduleManager');
        
        $modules = $moduleManager->getActiveModules();
        
        $translator = $sm->get('translator');
        
        foreach ($modules as $moduleKey => $moduleConfig) {
            if (!empty($moduleConfig['methods'])) {
                foreach ($moduleConfig['methods'] as $method => $methodConfig) {
                    if (isset($methodConfig['type']) && $methodConfig['type'] == 'be') {
                        $resources[] = array(
                            'resource' => 'be_method_access',
                            'privelege' => $moduleKey . ':' . $method,
                            'name' => sprintf($translator->translate('Be method access %s'), $translator->translateI18n($methodConfig['title'])),
                        );
                    }
                }
            }
        }
        
        return $resources;
    }
}
