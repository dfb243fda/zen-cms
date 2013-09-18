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
        $formsMerger = $this->serviceManager->get('App\Form\FormsMerger');
        
        $baseForm = $this->serviceManager->get('FormElementManager')
                                     ->get('Menu\Form\BaseMenuForm');  
        
        $formsMerger->addForm($baseForm);
        
        $formData = array();
        
        if (null !== $this->objectTypeId) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $objectType = $objectTypesCollection->getType($this->objectTypeId);     
            
            $formsMerger->addForm($objectType->getForm(false, true));
        }  
               
        $formData['common']['type_id'] = $this->objectTypeId;
        
        $form = $formsMerger->getForm();
        $form->setData($formData);
        
        return $form;
    }
}