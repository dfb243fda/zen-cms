<?php

namespace App\Form;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Form\Factory;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterInterface;

/**
 * Класс нуждается в доработке!
 */
class FormsMerger implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $forms = array();
    
    protected $inputFilters = array();
    
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
    
    public function addForm($formOrFieldset)
    {
        if (is_array($formOrFieldset)) {
            $formOrFieldset = $this->getFormFactory()->createForm($formOrFieldset);
        }

        if ($formOrFieldset instanceof Form) {
            $form = $formOrFieldset;
        } elseif ($formOrFieldset instanceof Fieldset) {
            $form = $this->getFormFactory()->createForm(array());
            $form->add($formOrFieldset);
        } else {
            throw new \Exception('dynamic form config must be \Zend\Form\Form or \Zend\Form\Fieldset object or an array');
        }
        
        $this->forms[] = $form;
    }
    
    public function addInputFilter($inputFilter)
    {
        if (is_array($inputFilter)) {
            $inputFilter = $this->getFilterFactory()->createInputFilter($inputFilter);
        }

        if (!$inputFilter instanceof InputFilterInterface) {
            throw new \Exception('dynamic form config input filter must be \Zend\InputFilter\InputFilterInterface object or an array');
        }  
        
        $this->inputFilters[] = $inputFilter;
    }
    
    public function getFormFactory()
    {
        if (null === $this->formFactory) {
            $this->formFactory = new Factory($this->serviceManager->get('FormElementManager'));
        }
        return $this->formFactory;
    }
    
    public function getFilterFactory()
    {
        if (null === $this->filterFactory) {
            $this->filterFactory = new InputFactory();
        }
        return $this->filterFactory;
    }
    
    public function getForm()
    {
        $form = $this->getFormFactory()->createForm(array());
        $inputFilter = $this->getFilterFactory()->createInputFilter(array());
        
        
        foreach ($this->forms as $tmpForm) {
            $tmpForm->setUseInputFilterDefaults(false);
            $this->mergeFilters($inputFilter, $tmpForm->getInputFilter());
            
            foreach ($tmpForm->getFieldsets() as $fieldset) {
                if ($form->has($fieldset->getName())) {
                    foreach ($fieldset->getElements() as $element) {
                        if (!$form->get($fieldset->getName())->has($element->getName())) {
                            $form->get($fieldset->getName())->add($element);
                        }                    
                    }                
                } else {
                    $form->add($fieldset);
                }            
            }
        }
        
        
        foreach ($this->inputFilters as $tmpInputFilter) {
            $this->mergeFilters($inputFilter, $tmpInputFilter);
        }
        
        $form->setInputFilter($inputFilter);
        
        return $form;
    }
    
    protected function mergeFilters(InputFilterInterface $filter1, InputFilterInterface $filter2)
    {                 
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