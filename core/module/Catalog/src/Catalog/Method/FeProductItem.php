<?php

namespace Catalog\Method;

use Pages\Entity\FeContentMethod;

class FeProductItem extends FeContentMethod
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
    }   
    
    public function main()
    {        
        $result = array(
            'success' => false,
        );
        
        $newsId = $this->params()->fromRoute('itemId');
        
        if ($newsId === null) {
            $result['errMsg'] = 'Не передан идентификатор товара';
            return $result;
        }
        
        $newsId = (int)$newsId;
               
        $object = $this->objectsCollection->getObject($newsId);
        
        if (!$object->isExists()) {
            $result['errMsg'] = 'Товар не найден';
            return $result;
        }
        
        $result = $object->getObjectData();
        $result['success'] = true;
        
        $objectId = $result['id'];
        $object = $this->objectsCollection->getObject($objectId);

        $objectType = $this->objectTypesCollection->getType($object->getTypeId());

        $result['fieldGroups'] = array();

        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $result['fieldGroups'][$v->getName()] = array();

            $fields = $v->getFields();
            foreach ($fields as $k2=>$v2) {      
                $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                $result['fieldGroups'][$v->getName()][$v2->getName()] = $property->getValue();
            }
        }            
        
        return $result;
    }
}