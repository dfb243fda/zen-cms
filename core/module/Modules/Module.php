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
        $permissionsService = $sm->get('Modules\Service\Permissions');
        return $permissionsService->getPermissionResources();
    }
}
