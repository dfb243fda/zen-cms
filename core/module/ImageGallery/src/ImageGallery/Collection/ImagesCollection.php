<?php

namespace ImageGallery\Collection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ImagesCollection implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $parentObjectId;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function setParentObjectId($parentObjectId)
    {
        $this->parentObjectId = $parentObjectId;
        return $this;
    }
    
    public function addImage($data)
    {
        $db = $this->serviceManager->get('db');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $galleryService = $this->serviceManager->get('ImageGallery\Service\ImageGallery');
        
        $parentObjectId = $this->parentObjectId;
        
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
        
        $objectTypeId = $insertBase['type_id'];
        $objectType = $objectTypesCollection->getType($objectTypeId);

        $typeIds = $galleryService->getImageTypeIds();
        $typeIds = array_map(function ($id) use ($db) {
            return $db->getPlatform()->quoteValue($id);
        }, $typeIds);
        
        $typeIdsStr = implode($typeIds);
        
        $sqlRes = $db->query('
            select max(sorting) as max_sorting 
            from ' . DB_PREF . 'objects 
            where parent_id = ? and type_id IN (' . $typeIdsStr . ')', array($parentObjectId))->toArray();

        
        if (empty($sqlRes)) {
            $sorting = 0;
        } else {
            $sorting = $sqlRes[0]['max_sorting'] + 1;
        }
        
        $objectId = $objectsCollection->addObject($insertBase['name'], $objectTypeId, $parentObjectId, $sorting);

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
    
    public function getForm($populateForm)
    {
        $formFactory = $this->serviceManager->get('ImageGallery\FormFactory\ImageFormFactory');
        $formFactory->setObjectTypeId($this->objectTypeId)
                    ->setPopulateForm($populateForm);
        return $formFactory->getForm();
    }
}