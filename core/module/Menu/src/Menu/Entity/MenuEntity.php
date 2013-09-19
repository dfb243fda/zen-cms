<?php

namespace Menu\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class MenuEntity implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $objectId;
    
    protected $objectTypeId;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function getForm($populateForm)
    {
        $formFactory = $this->serviceManager->get('Menu\FormFactory\MenuFormFactory');
        $formFactory->setObjectTypeId($this->objectTypeId)
                    ->setObjectId($this->objectId)
                    ->setPopulateForm($populateForm);
        return $formFactory->getForm();
    }
    
    public function editMenu($data)
    {
        $insertFields = array();
        $insertBase = array();

        foreach ($data as $groupKey=>$groupData) {
            foreach ($groupData as $fieldName=>$fieldVal) {
                if ('field_' == substr($fieldName, 0, 6)) {
                    $insertFields[substr($fieldName, 6)] = $fieldVal;
                } else {
                    $insertBase[$fieldName] = $fieldVal;
                }
            }
        }
        
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        
        $object = $objectsCollection->getObject($this->objectId);
                
        $object->setName($insertBase['name'])->setTypeId($this->objectTypeId)->save();

        $objectType = $objectTypesCollection->getType($this->objectTypeId);
        
        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (array_key_exists($k2, $insertFields)) {
                    $property = $objectPropertyCollection->getProperty($this->objectId, $k2); 
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }
        
        return true;
    }
}