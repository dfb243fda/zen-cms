<?php

namespace Config\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Form\Factory;
use Config\Exception\ConfigException;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterInterface;

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
    
    protected $formFactory;
    
    protected $filterFactory;
    
    
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
        $this->formFactory = new Factory($this->serviceManager->get('FormElementManager'));
        $this->filterFactory = new InputFactory();
        
        $config = $this->serviceManager->get('config');
        $moduleManager = $this->serviceManager->get('moduleManager');
        $configManager = $this->serviceManager->get('configManager');
        
        $currentTab = $this->tab;
        
        $form = new Form();
        $form->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
        
        $inputFilter = $this->filterFactory->createInputFilter(array());
        
        
        
        //create form instance from config.php files
        if (isset($config['dynamic_config']['form'][$currentTab])) {
            $this->mergeForms($form, $config['dynamic_config']['form'][$currentTab], $inputFilter);
        }                
        
        //create input_filter instance from config.php files
        if (isset($config['dynamic_config']['input_filter'][$currentTab])) {            
            $this->mergeFilters($inputFilter, $config['dynamic_config']['input_filter'][$currentTab]);
        }        
        
        
        $modules = $moduleManager->getActiveModules();
        
        foreach ($modules as $moduleKey => $moduleConfig) {
            $className = $moduleKey . '\Module';
            $instance = new $className;
            
            if (method_exists($instance, 'getDynamicConfig')) {
                $tmpConfig = $instance->getDynamicConfig($this->serviceManager);
                
                if (isset($tmpConfig['form'][$currentTab])) {                    
                    $this->mergeForms($form, $tmpConfig['form'][$currentTab], $inputFilter);
                }   
                
                if (isset($tmpConfig['input_filter'][$currentTab])) {
                    $this->mergeFilters($inputFilter, $tmpConfig['input_filter'][$currentTab]);
                }   
            }
        }
                   
        $form->setInputFilter($inputFilter);
        
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
    
    protected function mergeForms(\Zend\Form\Form $form1, $form2, $inputFilter)
    {            
        if (is_array($form2)) {
            $form2 = $this->formFactory->createForm($form2);
        }

        if ($form2 instanceof Form) {
            $tmpForm = $form2;
        } elseif ($form2 instanceof Fieldset) {
            $tmpForm = new Form();
            $tmpForm->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
            $tmpForm->add($form2);
        } else {
            throw new ConfigException('dynamic form config must be \Zend\Form\Form or \Zend\Form\Fieldset object or an array');
        }
        
//        $this->mergeFilters($inputFilter, $tmpForm->getInputFilter());
        
        
        foreach ($tmpForm->getFieldsets() as $fieldset) {
            if ($form1->has($fieldset->getName())) {
                foreach ($fieldset->getElements() as $element) {
                    if (!$form1->get($fieldset->getName())->has($element->getName())) {
                        $form1->get($fieldset->getName())->add($element);
                    }                    
                }                
            } else {
                $form1->add($fieldset);
            }            
        }
    }
    
    protected function mergeFilters(InputFilterInterface $filter1, $filter2)
    {                    
        if (is_array($filter2)) {
            $filter2 = $this->filterFactory->createInputFilter($filter2);
        }

        if (!$filter2 instanceof InputFilterInterface) {
            throw new ConfigException('dynamic form config input filter must be \Zend\InputFilter\InputFilterInterface object or an array');
        }  
        
        foreach ($filter2->getInputs() as $inputFilterKey=>$inputFilter) {            
            if (!$filter1->has($inputFilterKey)) {                
                $filter1->add($inputFilter, $inputFilterKey);                
            } else {
                foreach ($inputFilter->getInputs() as $inputKey=>$input) {
                    $filter1->get($inputFilterKey)->add($input, $inputKey);
                }  
            }                      
        }   
        
 /*       foreach ($filter2->getInputs() as $inputFilterKey=>$inputFilter) {            
            if (!$filter1->getInputFilter()->has($inputFilterKey)) {                
                $filter1->getInputFilter()->add($inputFilter, $inputFilterKey);                
            } else {
                foreach ($inputFilter->getInputs() as $inputKey=>$input) {
                    $filter1->getInputFilter()->get($inputFilterKey)->add($input, $inputKey);
                }  
            }                      
        }   
  * 
  */
    }
    
}