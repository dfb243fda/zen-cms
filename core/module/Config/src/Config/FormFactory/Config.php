<?php

namespace Config\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Config implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $tab;
    
    /**
     * GetForm() method will return form with data or not
     * @var boolean
     */
    protected $populateForm = true;    
    
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
    
    public function setPopulateForm($populateForm)
    {
        $this->populateForm = (bool)$populateForm;
        return $this;
    }
    
    public function getPopulateForm()
    {
        return $this->populateForm;
    }
    
    
    public function getForm()
    {
        $formsMerger = $this->serviceManager->get('App\Form\FormsMerger');
        
        $config = $this->serviceManager->get('config');
        $moduleManager = $this->serviceManager->get('moduleManager');
        $configManager = $this->serviceManager->get('configManager');
        
        $currentTab = $this->tab;
        
        //create form instance from config.php files
        if (isset($config['dynamic_config']['form'][$currentTab])) {
            $formsMerger->addForm($config['dynamic_config']['form'][$currentTab]);
        }                
        
        //create input_filter instance from config.php files
        if (isset($config['dynamic_config']['input_filter'][$currentTab])) {     
            $formsMerger->addInputFilter($config['dynamic_config']['input_filter'][$currentTab]);
        }    
        
        $modules = $moduleManager->getActiveModules();
        
        foreach ($modules as $moduleKey => $moduleConfig) {
            $className = $moduleKey . '\Module';
            $instance = new $className;
            
            if (method_exists($instance, 'getDynamicConfig')) {
                $tmpConfig = $instance->getDynamicConfig($this->serviceManager);
                
                if (isset($tmpConfig['form'][$currentTab])) {   
                    $formsMerger->addForm($tmpConfig['form'][$currentTab]);
                }   
                
                if (isset($tmpConfig['input_filter'][$currentTab])) {
                    $formsMerger->addInputFilter($tmpConfig['input_filter'][$currentTab]);
                }   
            }
        }
        
        
        $form = $formsMerger->getForm();
        
        if ($this->populateForm) {
            foreach ($form->getFieldsets() as $fieldset) {
                foreach ($fieldset->getFieldsets() as $k=>$subFieldset) {
                    $subFieldset->populateValues($configManager->get($fieldset->getName(), $k, array()));
                }
                foreach ($fieldset->getElements() as $k=>$element) {
                    $element->setValue($configManager->get($fieldset->getName(), $k));
                }
            }  
        }
        
        
        return $form;
    }    
}