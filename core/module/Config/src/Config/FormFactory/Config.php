<?php

namespace Config\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Form\Factory;

class Config implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $tab;
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setTab($tab)
    {
        $this->tab = $tab;
        return $this;
    }
    
    public function getForm()
    {
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
        
        $currentTab = $this->tab;
        
        if (isset($dynamicConfig['form'][$currentTab])) {
            $formConfig = $dynamicConfig['form'][$currentTab];
        } else {
            $formConfig = array();
        }
                
        $configManager = $this->serviceManager->get('configManager');
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));
        
        $form = $factory->createForm($formConfig);
        
        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($fieldset->getElements() as $k=>$element) {
                $element->setValue($configManager->get($fieldset->getName(), $k));
            }
        }  
        
        return $form;
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