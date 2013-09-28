<?php

namespace News\Method;

use Pages\AbstractMethod\FeContentMethod;

class FeNewsList extends FeContentMethod
{
    public function main()
    {        
        $db = $this->serviceLocator->get('db');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        $objectPropertyCollection = $this->serviceLocator->get('objectPropertyCollection');
        $newsUrlService = $this->serviceLocator->get('News\Service\NewsUrl');
        
        $result = array();
        
        $rubricsId = $this->contentData['fieldGroups']['common']['fields']['news_rubric_id'];
        
        $rubricsId = array_map(function($v) use ($db) {
            return $db->getPlatform()->quoteValue($v);
        }, $rubricsId);
        
        $pageNum = (int)$this->params()->fromQuery('p', 1);
        if ($pageNum < 1) {
            $pageNum = 1;
        }
        $itemsPerPage = 3;
        
        $sqlRes = $db->query('
            select count(id) as cnt
            from ' . DB_PREF . 'objects 
            where parent_id IN (' . implode(', ', $rubricsId) . ') 
                and is_active = 1 
                and is_deleted = 0', array())->toArray();
        
        $totalCount = $sqlRes[0]['cnt'];
        
        $sqlRes = $db->query('
            select * 
            from ' . DB_PREF . 'objects 
            where parent_id IN (' . implode(', ', $rubricsId) . ') 
                and is_active = 1 
                and is_deleted = 0 
            order by created_time desc
            limit ' . ($itemsPerPage * ($pageNum - 1)) . ', ' . $itemsPerPage, array())->toArray();
                     
        foreach ($sqlRes as $row) {
            $objectId = $row['id'];
            $object = $objectsCollection->getObject($objectId);

            $objectType = $objectTypesCollection->getType($object->getTypeId());
            
            $row['link'] = $newsUrlService->getNewsItemUrl($objectId);

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
        
        return array(
            'items' => $result,
            'totalCount' => $totalCount,
            'itemsPerPage' => $itemsPerPage,
            'pageNum' => $pageNum,
        );
    }
    
    
}