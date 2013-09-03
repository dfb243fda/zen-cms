<?php

namespace Modules\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class ModulesList implements ServiceManagerAwareInterface
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
    
    public function getModulesList()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $translator = $this->serviceManager->get('translator');
        
        $modulesList = $moduleManager->getAllModulesList();               
        
        $modules = array();
        foreach ($modulesList as $moduleKey => $modulePath) {
            $modules[$moduleKey] = array();
            
            $moduleConfig = $moduleManager->getModuleConfig($moduleKey);
            $modules[$moduleKey]['version'] = $moduleConfig['version'];
            if ($moduleManager->isModuleInstalled($moduleKey)) {                
                $modules[$moduleKey]['is_required'] = $moduleManager->isModuleRequired($moduleKey);
                if ($moduleManager->isModuleActive($moduleKey)) {
                    $modules[$moduleKey]['title'] = $translator->translateI18n($moduleConfig['title']);   
                    $modules[$moduleKey]['status'] = array(
                        'key' => 'installed_and_activated',
                        'description' => $translator->translate('Module installed and activated')
                    );
                } else {
                    $modules[$moduleKey]['title'] = $translator->translate('[Unknown module name]');
                    $modules[$moduleKey]['status'] = array(
                        'key' => 'installed_and_deactivated',
                        'description' => $translator->translate('Module installed and deactivated')
                    );
                }
            } else {
                $modules[$moduleKey]['title'] = $translator->translate('[Unknown module name]');
                $modules[$moduleKey]['is_required'] = false;
                $modules[$moduleKey]['status'] = array(
                    'key' => 'not_installed',
                    'description' => $translator->translate('Module not installed')
                );
            }
        }
        
        return $modules;
    }
}