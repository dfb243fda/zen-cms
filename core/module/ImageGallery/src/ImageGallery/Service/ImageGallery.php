<?php

namespace ImageGallery\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ImageGallery implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $galleryTypeIds;
    protected $imageTypeIds;
    
    protected $galleryGuid = 'image-gallery';
    protected $imageGuid   = 'image';
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getGalleryTypeIds()
    {
        if (null === $this->galleryTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->galleryGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->galleryTypeIds = $typeIds;
        }
        return $this->galleryTypeIds;
    }
    
    public function getImageTypeIds()
    {
        if (null === $this->imageTypeIds) {
            $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
            
            $typeIds = array();
            $objectType = $objectTypesCollection->getType($this->imageGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->imageTypeIds = $typeIds;
        }
        return $this->imageTypeIds;
    }
    
    public function getTypeIds()
    {
        return array_merge($this->getGalleryTypeIds(), $this->getImageTypeIds());
    }
    
    public function isObjectGallery($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($objectId)) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getGalleryTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function isObjectImage($objectId)
    {            
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        if ($object = $objectsCollection->getObject($objectId)) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getImageTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function getGalleryGuid()
    {
        return $this->galleryGuid;
    }
    
    public function getImageGuid()
    {
        return $this->imageGuid;
    }
    
}