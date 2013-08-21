<?php

namespace Menu\Method;

use Pages\Entity\FeContentMethod;

class SingleMenu extends FeContentMethod
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

    public function main($menuId = 0)
    {        
        $result = array();
        
        if (0 == $menuId) {
            $menuId = $this->contentData['fieldGroups']['item-info']['fields']['menu_id'];
        }
        
        $sqlRes = $this->db->query('select * from ' . DB_PREF . 'objects where parent_id = ? and is_active = 1 and is_deleted = 0', array($menuId))->toArray();
               

        $parentsId = array();
        
        foreach ($sqlRes as $row) {
            $objectId = $row['id'];
            $object = $this->objectsCollection->getObject($objectId);

            $objectType = $this->objectTypesCollection->getType($object->getTypeId());

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
            
            $sqlRes = $this->db->query('
                select t1.id, t1.alias , t2.name 
                from ' . DB_PREF . 'pages t1, ' . DB_PREF . 'objects t2 
                where t1.id = ? and t2.id = t1.object_id', array($row['fieldGroups']['item-info']['page']))->toArray();            
            
            if (!empty($sqlRes)) {
                $pageAlias = $sqlRes[0]['alias'];
                if (!$pageAlias) {
                    $pageAlias = $sqlRes[0]['name'];
                }
                $row['link'] = $this->url()->fromRoute('fe', array(
                    'pageId' => $sqlRes[0]['id'],
                    'pageAlias' => $pageAlias,
                ));
            }
            
            
            $result[] = $row;
            $parentsId[] = $row['id'];
        }
        
        return array(
            'items' => $result,
        );
    }
    
    
}