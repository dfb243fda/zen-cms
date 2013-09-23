<?php

namespace Catalog\Method;

use Pages\AbstractMethod\FeContentMethod;

class FeProductItem extends FeContentMethod
{    
    public function main()
    {   
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceLocator->get('objectPropertyCollection');
        
        $result = array(
            'success' => false,
        );
        
        $newsId = $this->params()->fromRoute('itemId');
        
        if ($newsId === null) {
            $result['errMsg'] = 'Не передан идентификатор товара';
            return $result;
        }
        
        $newsId = (int)$newsId;
        
        if (!$object = $objectsCollection->getObject($newsId)) {
            $result['errMsg'] = 'Товар не найден';
            return $result;
        }
        
        $result = $object->getObjectData();
        $result['success'] = true;
        
        $objectId = $result['id'];
        $object = $objectsCollection->getObject($objectId);

        $objectType = $objectTypesCollection->getType($object->getTypeId());

        $result['fieldGroups'] = array();

        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $result['fieldGroups'][$v->getName()] = array();

            $fields = $v->getFields();
            foreach ($fields as $k2=>$v2) {      
                $property = $objectPropertyCollection->getProperty($objectId, $k2);                        
                $result['fieldGroups'][$v->getName()][$v2->getName()] = $property->getValue();
            }
        }            
        
        return $result;
    }
}