<?php

namespace Catalog\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Catalog implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $categoryTypeIds;
    protected $productTypeIds;
    
    protected $categoryGuid = 'category';
    protected $productGuid   = 'product';
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getCategoryTypeIds()
    {
        if (null === $this->categoryTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->categoryGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->categoryTypeIds = $typeIds;
        }
        return $this->categoryTypeIds;
    }
    
    public function getProductTypeIds()
    {
        if (null === $this->productTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->productGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->productTypeIds = $typeIds;
        }
        return $this->productTypeIds;
    }
    
    public function getTypeIds()
    {
        return array_merge($this->getCategoryTypeIds(), $this->getProductTypeIds());
    }
    
    public function isObjectCategory($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($objectId)) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getCategoryTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function isObjectProduct($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($objectId)) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getProductTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function getCategoryGuid()
    {
        return $this->categoryGuid;
    }
    
    public function getProductGuid()
    {
        return $this->productGuid;
    }
    
}