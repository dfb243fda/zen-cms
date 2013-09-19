<?php

namespace Menu\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Menu implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $menuTypeIds;
    protected $itemTypeIds;
    
    protected $menuGuid = 'menu';
    protected $itemGuid   = 'menu-item';
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getMenuTypeIds()
    {
        if (null === $this->menuTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->menuGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->menuTypeIds = $typeIds;
        }
        return $this->menuTypeIds;
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
        return array_merge($this->getMenuTypeIds(), $this->getItemTypeIds());
    }
    
    public function isObjectMenu($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($objectId)) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getMenuTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function isObjectItem($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($objectId)) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getItemTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function getMenuGuid()
    {
        return $this->menuGuid;
    }
    
    public function getItemGuid()
    {
        return $this->itemGuid;
    }
    
}