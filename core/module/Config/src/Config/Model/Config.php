<?php

namespace Config\Model;

use Zend\Validator\AbstractValidator;
use Zend\Form\Factory;

class Config
{
    protected $serviceManager;
    
    protected $currentTab;
    
    protected $translator;
    
    protected $configManager;
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        
        $this->translator = $this->serviceManager->get('translator');
        $this->configManager = $this->serviceManager->get('configManager');
        
        AbstractValidator::setDefaultTranslator($this->translator);
    }
    
    public function setCurrentTab($currentTab)
    {
        $this->currentTab = $currentTab;
        return $this;
    }
    
    public function init()
    {
        $config = $this->serviceManager->get('config');
        if (isset($config['dynamic_config'])) {
            $dynamicConfig = $config['dynamic_config'];
        } else {
            $dynamicConfig = array();
        }
        
        $modules = $this->serviceManager->get('moduleManager')->getActiveModules();
        
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
            $tabs[$k]['title'] = $this->translator->translateI18n($v['title']);
            
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
            $currentTab = key($tabs);
        } else {
            $currentTab = (string)$this->currentTab;
            if (!isset($tabs[$k])) {
                throw new \Exception('tab ' . $k . ' not found');
            }
        }
        $tabs[$currentTab]['active'] = true;
        
        if (isset($dynamicConfig['form'][$currentTab])) {
            $formConfig = $dynamicConfig['form'][$currentTab];
        } else {
            $formConfig = array();
        }
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));
        
        $form = $factory->createForm($formConfig);
        $form->prepare();
        
        $formValues = array();
        
        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($fieldset->getElements() as $k=>$element) {
                $formValues[$fieldset->getName()][$k] = $this->configManager->get($fieldset->getName(), $k);
            }
        }  
        
        $this->tabs = $tabs;
        $this->zendForm = $form;
        $this->formConfig = $formConfig;
        $this->formValues = $formValues;
    }
    
    public function getTabs()
    {
        return $this->tabs;
    }
    
    public function getForm()
    {
        return array(
            'formConfig' => $this->formConfig,
            'formValues' => $this->formValues,
        );
    }
    
    public function edit($data)
    {
        $form = $this->zendForm;
        
        $form->setData($data);
        
        if ($form->isValid()) { 
            $data = $form->getData();
                
            foreach ($data as $k=>$v) {
                foreach ($v as $k2=>$v2) {
                    $this->configManager->set($k, $k2, $v2);
                }
            }
            
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
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