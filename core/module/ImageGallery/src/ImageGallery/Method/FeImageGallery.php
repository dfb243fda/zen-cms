<?php

namespace ImageGallery\Method;

use Pages\AbstractMethod\FeContentMethod;

class FeImageGallery extends FeContentMethod
{
    public function main()
    {        
        $db = $this->serviceLocator->get('db');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        $objectPropertyCollection = $this->serviceLocator->get('objectPropertyCollection');
        
        $result = array();
        
        $galleryId = $this->contentData['fieldGroups']['common']['fields']['image_gallery_id'];
        
        $sqlRes = $db->query('
            select * 
            from ' . DB_PREF . 'objects 
            where parent_id = ?
                and is_active = 1 
                and is_deleted = 0 
            order by created_time desc', array($galleryId))->toArray();
                     
        foreach ($sqlRes as $row) {
            $objectId = $row['id'];
            $object = $objectsCollection->getObject($objectId);

            $objectType = $objectTypesCollection->getType($object->getTypeId());

            $row['fieldGroups'] = array();

            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $row['fieldGroups'][$v->getName()] = array();

                $fields = $v->getFields();
                foreach ($fields as $k2=>$v2) {      
                    $property = $objectPropertyCollection->getProperty($objectId, $k2);                        
                    $row['fieldGroups'][$v->getName()][$v2->getName()] = $property->getValue();
                }
            }            
            
            $result[] = $row;
        }
        
        foreach ($result as $k=>$row) {
            $imgUrl = $result[$k]['fieldGroups']['common']['image_src'];
            
            $result[$k]['fieldGroups']['common']['image_path'] = PUBLIC_PATH . substr($imgUrl, strlen(ROOT_URL_SEGMENT));
        }
        
        return array(
            'gallery' => $galleryId,
            'items' => $result,
        );
    }
    
    
}