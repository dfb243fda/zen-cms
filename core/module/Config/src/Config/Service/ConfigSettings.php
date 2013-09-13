<?php

namespace Config\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ConfigSettings implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $currentTab;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setCurrentTab($currentTab)
    {
        $this->currentTab = $currentTab;
        return $this;
    }
    
    public function getTabs()
    {
        $translator = $this->serviceManager->get('translator');
        $config = $this->serviceManager->get('config');
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        if (isset($config['dynamic_config']['tabs'])) {
            $tabs = $config['dynamic_config']['tabs'];
        } else {
            $tabs = array();
        }
        
        $modules = $moduleManager->getActiveModules();
        
        foreach ($modules as $moduleKey => $moduleConfig) {
            $className = $moduleKey . '\Module';
            $instance = new $className;
            
            if (method_exists($instance, 'getDynamicConfig')) {
                $dynamicConfig = $instance->getDynamicConfig($this->serviceManager);
                if (isset($dynamicConfig['tabs'])) {
                    $tabs = array_merge($tabs, $dynamicConfig['tabs']);
                }
            }
        }
        
        foreach ($tabs as $k=>$v) {
            $tabs[$k]['title'] = $translator->translateI18n($v['title']);
            
            $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
            
            $tabs[$k]['link'] = $urlPlugin->fromRoute('admin/method', array(
                'module' => 'Config',
                'method' => 'DynamicConfig',
                'id'     => $k,
            ));
        }
        
        if (empty($tabs)) {
            throw new \Exception('there is no dynamic config');
        }
        
        if (null === $this->currentTab) {
            reset($tabs);
            $currentTab = $this->currentTab = key($tabs);
        } else {
            $currentTab = (string)$this->currentTab;
            if (!isset($tabs[$k])) {
                throw new \Exception('tab ' . $k . ' not found');
            }
        }
        $tabs[$currentTab]['active'] = true;
        
        return $tabs;
    }    
    
    public function getForm($populateForm)
    {
        $formFactory = $this->serviceManager->get('Config\FormFactory\Config');
        
        $form = $formFactory->setTab($this->currentTab)
            ->setPopulateForm($populateForm)
            ->getForm();
        
        return $form;
    }
    
    public function edit($data)
    {            
        $configManager = $this->serviceManager->get('configManager');
        foreach ($data as $k=>$v) {
            foreach ($v as $k2=>$v2) {
                $configManager->set($k, $k2, $v2);
            }
        }

        return true;
    }
    
}