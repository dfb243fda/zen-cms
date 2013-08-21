<?php

namespace Modules\Method;

use App\Method\AbstractMethod;

class ModulesList extends AbstractMethod
{
    protected $moduleManager;
    
    protected $translator;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->moduleManager = $this->rootServiceLocator->get('ModuleManager');
        $this->translator = $this->rootServiceLocator->get('translator');
    }
        
    public function main()
    {        
        $modulesList = $this->moduleManager->getAllModulesList();               
        
        $modules = array();
        foreach ($modulesList as $moduleKey => $modulePath) {
            $modules[$moduleKey] = array();
            
            $moduleConfig = $this->moduleManager->getModuleConfig($moduleKey);
            $modules[$moduleKey]['version'] = $moduleConfig['version'];
            if ($this->moduleManager->isModuleInstalled($moduleKey)) {                
                $modules[$moduleKey]['is_required'] = $this->moduleManager->isModuleRequired($moduleKey);
                if ($this->moduleManager->isModuleActive($moduleKey)) {
                    $modules[$moduleKey]['title'] = $this->translator->translateI18n($moduleConfig['title']);   
                    $modules[$moduleKey]['status'] = array(
                        'key' => 'installed_and_activated',
                        'description' => $this->translator->translate('Module installed and activated')
                    );
                } else {
                    $modules[$moduleKey]['title'] = $this->translator->translate('[Unknown module name]');
                    $modules[$moduleKey]['status'] = array(
                        'key' => 'installed_and_deactivated',
                        'description' => $this->translator->translate('Module installed and deactivated')
                    );
                }
            } else {
                $modules[$moduleKey]['title'] = $this->translator->translate('[Unknown module name]');
                $modules[$moduleKey]['is_required'] = false;
                $modules[$moduleKey]['status'] = array(
                    'key' => 'not_installed',
                    'description' => $this->translator->translate('Module not installed')
                );
            }
        }
                
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/Modules/modules_list.phtml',
                'data' => array(
                    'modules' => $modules,
                ),
            ),
        );
        
        return $result;
    }
}