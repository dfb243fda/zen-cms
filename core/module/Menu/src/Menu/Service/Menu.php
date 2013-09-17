<?php

namespace Menu\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Menu implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $rubricTypeIds;
    protected $itemTypeIds;
    
    protected $rubricGuid = 'menu';
    protected $itemGuid   = 'menu-item';
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getRubricTypeIds()
    {
        if (null === $this->rubricTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->rubricGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->rubricTypeIds = $typeIds;
        }
        return $this->rubricTypeIds;
    }
    
    public function getItemTypeIds()
    {
        if (null === $this->itemTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->itemGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->itemTypeIds = $typeIds;
        }
        return $this->itemTypeIds;
    }
    
    public function getTypeIds()
    {
        return array_merge($this->getRubricTypeIds(), $this->getItemTypeIds());
    }
    
    public function isObjectRubric($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        $object = $objectsCollection->getObject($objectId);
        
        if ($object->isExists()) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getRubricTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function isObjectItem($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        $object = $objectsCollection->getObject($objectId);
        
        if ($object->isExists()) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getItemTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function getRubricGuid()
    {
        return $this->rubricGuid;
    }
    
    public function getItemGuid()
    {
        return $this->itemGuid;
    }
    
}