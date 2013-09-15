<?php

namespace ObjectTypes\Collection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FieldsAdminCollection implements ServiceManagerAwareInterface
{    
    protected $serviceManager;    
    
    protected $objectTypeId;
    
    protected $fieldsGroupId;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($typeId)
    {
        $this->objectTypeId = $typeId;
        return $this;
    }
    
    public function setFieldsGroupId($groupId)
    {
        $this->fieldsGroupId = $groupId;
        return $this;
    }
    
    public function addField($data)
    {
        $fieldsCollection = $this->serviceManager->get('FieldsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $objectType = $objectTypesCollection->getType($this->objectTypeId);
        $fieldsGroup = $objectType->getFieldsGroup($this->fieldsGroupId);
        
        $result = array();
        
        $fieldId = $fieldsCollection->addField($data);

        $field = $fieldsCollection->getField($fieldId);

        $groupName = $fieldsGroup->getName();

        $fieldsGroup->attachField($fieldId);

        $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($this->objectTypeId);
        foreach ($descendantTypeIds as $v) {
            $tmpObjectType = $objectTypesCollection->getType($v);

            $tmpFieldsGroup = $tmpObjectType->getFieldsGroupByName($groupName);

            if (null !== $tmpFieldsGroup) {
                $tmpFieldsGroup->attachField($fieldId);
            }
        }            
        
        $result['success'] = true;
        $result['fieldId'] = $fieldId;
        $result['field'] = $field;
        
        return $result;
    }
}