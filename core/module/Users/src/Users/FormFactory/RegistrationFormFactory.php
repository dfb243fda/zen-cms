<?php

namespace Users\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class RegistrationFormFactory implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $onlyVisible = false;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function setOnlyVisible($onlyVisible)
    {
        $this->onlyVisible = $onlyVisible;
        return $this;
    }
    
    public function getForm()
    {
        $formsMerger = $this->serviceManager->get('App\Form\FormsMerger');
        $formElementManager = $this->serviceManager->get('formElementManager');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $baseForm = $formElementManager->get('Users\Form\RegistrationBaseForm');
        
        $formsMerger->addForm($baseForm);
        
        if (null !== $this->objectTypeId) {
            $objectType = $objectTypesCollection->getType($this->objectTypeId);                    
            $formsMerger->addForm($objectType->getForm($this->onlyVisible));
        }
        
        return $formsMerger->getForm();
    }
}