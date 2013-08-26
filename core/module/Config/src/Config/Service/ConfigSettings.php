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
        
        if (isset($config['dynamic_config'])) {
            $dynamicConfig = $config['dynamic_config'];
        } else {
            $dynamicConfig = array();
        }
        
        $modules = $moduleManager->getActiveModules();
        
        foreach ($modules as $moduleKey => $moduleConfig) {
            $className = $moduleKey . '\Module';
            $instance = new $className;
            
            if (method_exists($instance, 'getDynamicConfig')) {
                $dynamicConfig = $this->mergeOptions($dynamicConfig, $instance->getDynamicConfig($this->serviceManager));
            }
        }
        
        $tabs = array();
        foreach ($dynamicConfig['tabs'] as $k=>$v) {
            $tabs[$k] = $v;
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
    
    public function getForm()
    {
        $formFactory = $this->serviceManager->get('Config\FormFactory\Config');
        
        $form = $formFactory->setTab($this->currentTab)->getForm();
        
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
    
    protected function mergeOptions(array $array1, $array2 = null)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key])) {
                    $array1[$key] = (array_key_exists($key, $array1) && is_array($array1[$key]))
                                  ? $this->mergeOptions($array1[$key], $array2[$key])
                                  : $array2[$key];
                } else {
                    $array1[$key] = $val;
                }
            }
        }
        return $array1;
    }   
    
}