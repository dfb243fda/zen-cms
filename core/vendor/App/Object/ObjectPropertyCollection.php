<?php

namespace App\Object;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ObjectPropertyCollection implements ServiceManagerAwareInterface
{
    protected $serviceManager;
  
    protected $serviceNames = array();

    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
  
    public function getProperty($objectId, $fieldId)
    {        
        $property = $this->serviceManager->get($this->getServiceNameByFieldId($fieldId));
        
        $property->setObjectId($objectId)->setFieldId($fieldId)->init();
        return $property;        
    }
    
    public function getServiceNameByFieldId($fieldId)
    {        
        $fieldsCollection = $this->serviceManager->get('fieldsCollection');
        $fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');
        
        if (!isset($this->serviceNames[$fieldId])) {
            $field = $fieldsCollection->getField($fieldId);
            $fieldTypeId = $field->getFieldTypeId();
            $fieldType = $fieldTypesCollection->getFieldType($fieldTypeId);
            $fieldTypeName = $fieldType->getName();

            $this->serviceNames[$fieldId] = 'ObjectProperty\\' . ucfirst($fieldTypeName);
        }
        return $this->serviceNames[$fieldId];
    }
   
}