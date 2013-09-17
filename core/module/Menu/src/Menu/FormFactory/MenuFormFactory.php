<?php

namespace Menu\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class MenuFormFactory implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $objectTypeId;
    
    /**
     * GetForm() method will return form with data or not
     * @var boolean
     */
    protected $populateForm = true;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function setPopulateForm($populateForm)
    {
        $this->populateForm = (bool)$populateForm;
        return $this;
    }
    
    public function getForm()
    {
        $form = $this->serviceManager->get('FormElementManager')
                                     ->get('Menu\Form\MenuForm');  
        
        $formData = array();
        
        if (null !== $this->objectTypeId) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $objectType = $objectTypesCollection->getType($this->objectTypeId);       
            $this->mergeForms($form, $objectType->getForm(false, true));
        }  
        
        $formData['common']['type_id'] = $this->objectTypeId;
        
        $form->setData($formData);
        
        return $form;
    }
    
    protected function mergeForms(\Zend\Form\Form $form1, \Zend\Form\Form $form2)
    {
        $form1->setUseInputFilterDefaults(false);
        
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