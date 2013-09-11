<?php

namespace Config\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Form\Factory;
use Config\Exception\ConfigException;

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
        $factory = new Factory($this->serviceManager->get('FormElementManager'));
        $configManager = $this->serviceManager->get('configManager');
        
        $currentTab = $this->tab;
        
        if (isset($config['dynamic_config']['form'][$currentTab])) {
            $formConfig = $config['dynamic_config']['form'][$currentTab];
        } else {
            $formConfig = array();
        }
        
        if (is_array($formConfig)) {
            $formConfig = $factory->createForm($formConfig);
        }
        
        if ($formConfig instanceof Form) {
            $form = $formConfig;
        } elseif ($formConfig instanceof Fieldset) {
            $form = new Form();
            $form->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
            $form->add($formConfig);
        } else {
            throw new ConfigException('dynamic form config must be \Zend\Form\Form or \Zend\Form\Fieldset object or an array');
        }
        
        $modules = $moduleManager->getActiveModules();
        
        foreach ($modules as $moduleKey => $moduleConfig) {
            $className = $moduleKey . '\Module';
            $instance = new $className;
            
            if (method_exists($instance, 'getDynamicConfig')) {
                $tmpConfig = $instance->getDynamicConfig($this->serviceManager);
                
                if (isset($tmpConfig['form'][$currentTab])) {
                    $tmpTabConfig = $tmpConfig['form'][$currentTab];
                    
                    if (is_array($tmpTabConfig)) {
                        $tmpTabConfig = $factory->createForm($tmpTabConfig);
                    }
                    
                    if ($tmpTabConfig instanceof Form) {
                        $tmpForm = $tmpTabConfig;
                    } elseif ($tmpTabConfig instanceof Fieldset) {
                        $tmpForm = new Form();
                        $tmpForm->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
                        $tmpForm->add($tmpTabConfig);
                    } else {
                        throw new ConfigException('getDynamicConfig must return \Zend\Form\Form object or \Zend\Form\Fieldset or an array');
                    }

                    $this->mergeForms($form, $tmpForm);
                }                
            }
        }
        
        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($fieldset->getFieldsets() as $k=>$subFieldset) {
                $subFieldset->populateValues($configManager->get($fieldset->getName(), $k));
            }
            foreach ($fieldset->getElements() as $k=>$element) {
                $element->setValue($configManager->get($fieldset->getName(), $k));
            }
        }  
        
        return $form;
    }
    
    protected function mergeForms(\Zend\Form\Form $form1, \Zend\Form\Form $form2)
    {
        foreach ($form2->getFieldsets() as $fieldset) {
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
        
        foreach ($form2->getInputFilter()->getInputs() as $inputFilterKey=>$inputFilter) {            
            if (!$form1->getInputFilter()->has($inputFilterKey)) {                
                $form1->getInputFilter()->add($inputFilter, $inputFilterKey);                
            } else {
                foreach ($inputFilter->getInputs() as $inputKey=>$input) {
                    $form1->getInputFilter()->get($inputFilterKey)->add($input, $inputKey);
                }  
            }                      
        }      
    }
    
}