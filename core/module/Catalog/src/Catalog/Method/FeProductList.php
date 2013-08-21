<?php

namespace Catalog\Method;

use Pages\Entity\FeContentMethod;

class FeProductList extends FeContentMethod
{
    public function init()
    {
        $rootServiceManager = $this->serviceLocator->getServiceLocator();
        
        $this->translator = $rootServiceManager->get('translator');
        $this->db = $rootServiceManager->get('db');
        $this->objectTypesCollection = $rootServiceManager->get('objectTypesCollection');
        $this->objectsCollection = $rootServiceManager->get('objectsCollection');
//        $this->menuModel = new Menu($this->serviceManager);
        $this->objectPropertyCollection = $rootServiceManager->get('objectPropertyCollection');
        $this->catalogService = $rootServiceManager->get('Catalog\Service\Catalog');
    }   

    public function main()
    {        
        $result = array();
        
        $categoryId = $this->contentData['fieldGroups']['item-info']['fields']['product_cat_id'];
                      
        $sqlRes = $this->db->query('select * from ' . DB_PREF . 'objects where parent_id = ? and is_active = 1 and is_deleted = 0 order by created_time desc', array($categoryId))->toArray();
                     
        foreach ($sqlRes as $row) {
            $objectId = $row['id'];
            $object = $this->objectsCollection->getObject($objectId);

            $objectType = $this->objectTypesCollection->getType($object->getTypeId());
            
            $row['link'] = $this->catalogService->getSingleProductUrl($objectId);

            $row['fieldGroups'] = array();

            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $row['fieldGroups'][$v->getName()] = array();

                $fields = $v->getFields();
                foreach ($fields as $k2=>$v2) {      
                    $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                    $row['fieldGroups'][$v->getName()][$v2->getName()] = $property->getValue();
                }
            }            
            
            $result[] = $row;
        }
        
        return array(
            'items' => $result,
        );
    }
    
    
}