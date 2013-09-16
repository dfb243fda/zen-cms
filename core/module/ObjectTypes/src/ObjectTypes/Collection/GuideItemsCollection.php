<?php

namespace ObjectTypes\Collection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class GuideItemsCollection implements ServiceManagerAwareInterface
{    
    protected $serviceManager;    
    
    protected $guideId;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setGuideId($guideId)
    {
        $this->guideId = $guideId;
        return $this;
    }
    
    public function addGuideItem($data)
    {
        $db = $this->serviceManager->get('db');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $objectType = $objectTypesCollection->getType($this->guideId);
        
        if (null === $objectType || !$objectType->getIsGuidable()) {
            return false;
        }
        
        
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

        $sqlRes = $db->query('
            select max(sorting) as max_sorting 
            from ' . DB_PREF . 'objects 
            where type_id = ?', array($this->guideId))->toArray();

        if (empty($sqlRes)) {
            $sorting = 0;
        } else {
            $sorting = $sqlRes[0]['max_sorting'] + 1;
        }

        $objectId = $objectsCollection->addObject($insertBase['name'], $this->guideId, 0, $sorting);

        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (array_key_exists($k2, $insertFields)) {
                    $property = $objectPropertyCollection->getProperty($objectId, $k2); 
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }

        return $objectId;
    }
    
    public function getGuideItem($guideItemId)
    {
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($guideItemId)) {
            if ($object->getType()->getIsGuidable()) {
                $guideItem = $this->serviceManager->get('ObjectTypes\Entity\GuideItem');
                $guideItem->setId($guideItemId);
                return $guideItem;
            }
        }
        return null;
    }
    
    public function deleteGuideItem($guideItemId)
    {
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($guideItemId)) {
            if ($object->getType()->getIsGuidable()) {
                return $objectsCollection->delObject($guideItemId);
            }
        }
        return false;
    }
    
}