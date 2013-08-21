<?php

namespace App\ObjectPropertyCollection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ObjectPropertyCollection implements ServiceManagerAwareInterface
{
    protected $serviceManager;
  
    protected $serviceNames = array();

    protected $initialized = false;
        
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    protected function init()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            
            $this->db = $this->serviceManager->get('db');
        
            $this->translator = $this->serviceManager->get('translator');        

            $this->objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            $this->fieldsCollection = $this->serviceManager->get('fieldsCollection');
            $this->fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');
        }
    }
  
    public function getProperty($objectId, $fieldId)
    {
        $this->init();
        
        $property = $this->serviceManager->get($this->getServiceNameByFieldId($fieldId));
        
        $property->setObjectId($objectId)->setFieldId($fieldId)->init();
        return $property;        
    }
    
    public function getServiceNameByFieldId($fieldId)
    {
        $this->init();
        
        if (!isset($this->serviceNames[$fieldId])) {
            $field = $this->fieldsCollection->getField($fieldId);
            $fieldTypeId = $field->getFieldTypeId();
            $fieldType = $this->fieldTypesCollection->getFieldType($fieldTypeId);
            $fieldTypeName = $fieldType->getName();

            $this->serviceNames[$fieldId] = 'ObjectProperty\\' . ucfirst($fieldTypeName);
        }
        return $this->serviceNames[$fieldId];
    }
    
}