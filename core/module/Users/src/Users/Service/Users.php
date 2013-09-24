<?php

namespace Users\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Users implements ServiceManagerAwareInterface
{
    protected $serviceManager;    
    
    protected $typeIds;
    
    protected $pagesGuid;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getTypeIds()
    {
        if (null === $this->typeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->pagesGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->typeIds = $typeIds;
        }
        return $this->typeIds;
    }
}