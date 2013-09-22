<?php

namespace Menu\Method;

use Pages\AbstractMethod\FeContentMethod;

class SingleMenu extends FeContentMethod
{
    public function main($menuId = 0)
    {        
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        $objectPropertyCollection = $this->serviceLocator->get('objectPropertyCollection');
        $db = $this->serviceLocator->get('db');
        $pageUrlService = $this->serviceLocator->get('Pages\Service\PageUrl');
        
        $result = array();
        
        if (0 == $menuId) {
            $menuId = $this->contentData['fieldGroups']['item-info']['fields']['menu_id'];
        }
        
        $sqlRes = $db->query('
            select *
            from ' . DB_PREF . 'objects
            where parent_id = ? and is_active = 1 and is_deleted = 0 
            order by sorting', array($menuId))->toArray();
               

        $parentsId = array();
        
        foreach ($sqlRes as $row) {
            $objectId = $row['id'];
            $object = $objectsCollection->getObject($objectId);

            $objectType = $object->getType();

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
            
            $row['link'] = $pageUrlService->getPageUrl($row['fieldGroups']['item-info']['page']);            
            
            $result[] = $row;
            $parentsId[] = $row['id'];
        }
        
        return array(
            'items' => $result,
        );
    }
    
    
}