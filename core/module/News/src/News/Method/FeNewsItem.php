<?php

namespace News\Method;

use Pages\AbstractMethod\FeContentMethod;

class FeNewsItem extends FeContentMethod
{    
    public function main()
    {   
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceLocator->get('objectPropertyCollection');        
        $newsService = $this->serviceLocator->get('News\Service\News');        
        
        $result = array();
        
        $newsId = $this->params()->fromRoute('itemId');
        
        if ($newsId === null) {
            $result['success'] = false;
            $result['errMsg'] = 'Не передан идентификатор новости';
            return $result;
        }
        
        $newsId = (int)$newsId;
        
        if (!$newsService->isObjectNews($newsId)) {
            $result['success'] = false;
            $result['errMsg'] = 'Новость не найдена';
            return $result;
        }
        
        $object = $objectsCollection->getObject($newsId);
        
        $result = $object->getObjectData();
        $result['success'] = true;

        $objectType = $objectTypesCollection->getType($object->getTypeId());

        $result['fieldGroups'] = array();

        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $result['fieldGroups'][$v->getName()] = array();

            $fields = $v->getFields();
            foreach ($fields as $k2=>$v2) {      
                $property = $objectPropertyCollection->getProperty($newsId, $k2);                        
                $result['fieldGroups'][$v->getName()][$v2->getName()] = $property->getValue();
            }
        }            
        
        return $result;
    }
}