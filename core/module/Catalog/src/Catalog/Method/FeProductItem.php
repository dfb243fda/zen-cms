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
        $catService = $this->serviceLocator->get('Catalog\Service\Catalog');        
        
        $result = array(
            'success' => false,
        );
        
        $prodId = $this->params()->fromRoute('itemId');
        
        if ($prodId === null) {
            $result['errMsg'] = 'Не передан идентификатор товара';
            return $result;
        }
        
        $prodId = (int)$prodId;
        
        if (!$catService->isObjectProduct($prodId)) {
            $result['errMsg'] = 'Товар не найден';
            return $result;
        }
        
        $object = $objectsCollection->getObject($prodId);
        
        $result = $object->getObjectData();
        $result['success'] = true;

        $objectType = $objectTypesCollection->getType($object->getTypeId());

        $result['fieldGroups'] = array();

        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $result['fieldGroups'][$v->getName()] = array();

            $fields = $v->getFields();
            foreach ($fields as $k2=>$v2) {      
                $property = $objectPropertyCollection->getProperty($prodId, $k2);                        
                $result['fieldGroups'][$v->getName()][$v2->getName()] = $property->getValue();
            }
        }            
        
        return $result;
    }
}