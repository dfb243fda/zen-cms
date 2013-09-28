<?php

namespace News\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class News implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $categoryTypeIds;
    protected $productTypeIds;
    
    protected $rubricGuid = 'news-rubric';
    protected $newsGuid   = 'news';
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getRubricTypeIds()
    {
        if (null === $this->categoryTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->rubricGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->categoryTypeIds = $typeIds;
        }
        return $this->categoryTypeIds;
    }
    
    public function getNewsTypeIds()
    {
        if (null === $this->productTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->newsGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->productTypeIds = $typeIds;
        }
        return $this->productTypeIds;
    }
    
    public function getTypeIds()
    {
        return array_merge($this->getRubricTypeIds(), $this->getNewsTypeIds());
    }
    
    public function isObjectRubric($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($objectId)) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getRubricTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function isObjectNews($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($objectId)) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getNewsTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function getRubricGuid()
    {
        return $this->rubricGuid;
    }
    
    public function getNewsGuid()
    {
        return $this->newsGuid;
    }
    
}