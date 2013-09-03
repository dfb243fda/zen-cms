<?php

namespace Modules\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Permissions implements ServiceManagerAwareInterface
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
    
    public function getPermissionResources()
    {
        $resources = array();
        
        $moduleManager = $this->serviceManager->get('moduleManager');
        $translator = $this->serviceManager->get('translator');
        
        $modules = $moduleManager->getActiveModules();
        
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