<?php

namespace App\Object;

use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ObjectTypesCollection implements ServiceManagerAwareInterface
{    
    protected $serviceManager;
            
    protected $types = array();
    
    protected $objectTypesTable = 'object_types';
    
    protected $objectFieldGroupsTable = 'object_field_groups';
    
    protected $fieldsController = 'fields_controller';
    
    protected $allTypesList;
    
    protected $childrenTypesList = array();
    
    protected $objectsTable = 'objects';
    
    protected $childrenTypes = array();
    
    protected $allTypes;
        
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getType($typeId)
    {        
        $db = $this->serviceManager->get('db');
        
        if (!is_numeric($typeId)) {
            $typeId = $this->getTypeIdByGuid($typeId);
            if (null === $typeId) {
                return null;
            }
        }
        
        if (!array_key_exists($typeId, $this->types)) { 
            $objectType = null;
            
            $sqlRes = $db->query('
                select *
                from ' . DB_PREF . $this->objectTypesTable . '
                where id = ?
            ', array($typeId))->toArray();
                        
            if (!empty($sqlRes)) {
                $typeData = $sqlRes[0];
                
                $objectType = $this->serviceManager->get('App\Object\ObjectType');
                $objectType->setId($typeId)
                           ->setGuid($typeData['guid'])
                           ->setName($typeData['name'])
                           ->setParentId($typeData['parent_id'])
                           ->setIsGuidable($typeData['is_guidable'])
                           ->setPageTypeId($typeData['page_type_id'])
                           ->setPageContentTypeId($typeData['page_content_type_id'])
                           ->setIsLocked($typeData['is_locked'])
                           ->init();
            }
            
            $this->types[$typeId] = $objectType;
        }      
        
        return $this->types[$typeId];
    }
    
    public function getTypeByGuid($guid)
    {        
        $id = $this->getTypeIdByGuid($guid);  
        if (null === $id) {
            return null;
        } else {
            return $this->getType($id);
        }        
    }
    
    public function getTypeIdByGuid($guid)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select id from ' . DB_PREF . $this->objectTypesTable . ' where guid = ? limit 1', array($guid))->toArray();
        
        if (empty($sqlRes)) {
            return null;
        } else {
            return $sqlRes[0]['id'];
        }
    }
    
    public function addType($parentId, $name, $isLocked = false)
    {
        $db = $this->serviceManager->get('db');
        
        $sql = new Sql($db);        
        $insert = $sql->insert(DB_PREF . $this->objectTypesTable);        
        $insert->values(array(
            'parent_id' => (int)$parentId,
            'name' => $name,
            'is_locked' => (int)$isLocked,
        ));        
        $sql->prepareStatementForSqlObject($insert)->execute();  
                
        $objectTypeId = $db->getDriver()->getLastGeneratedValue();
        
        $sqlRes = $db->query('select * from ' . DB_PREF . $this->objectFieldGroupsTable . ' where object_type_id = ?', array($parentId))->toArray();
        
        foreach ($sqlRes as $row) {
            $sql = new Sql($db);        
            $insert = $sql->insert(DB_PREF . $this->objectFieldGroupsTable);        
            $insert->values(array(
                'name' => $row['name'],
                'title' => $row['title'],
                'object_type_id' => $objectTypeId,
                'is_locked' => $row['is_locked'],
                'sorting' => $row['sorting'],
            ));        
            $sql->prepareStatementForSqlObject($insert)->execute();  
                        
            $fieldGroupId = $db->getDriver()->getLastGeneratedValue();
            
            $db->query('INSERT INTO ' . DB_PREF . $this->fieldsController . ' 
                (sorting, field_id, group_id) 
                SELECT sorting, field_id, ? 
                    FROM ' . DB_PREF . $this->fieldsController . '
                    WHERE group_id = ?', array($fieldGroupId, $row['id']));
        }
        
        $pageTypeId = 0;
        $parentPageContentTypeId = false;
        if ($parentId) {
            $parentObjectType = $this->getType($parentId);
            if (null !== $parentObjectType) {
                $pageTypeId = $parentObjectType->getPageTypeId();
                $parentPageContentTypeId = $parentObjectType->getPageContentTypeId();
            }
        }
        
        $objectType = $this->getType($objectTypeId);
        
        if ($pageTypeId) {
            $objectType->setPageTypeId($pageTypeId);
        }
        if ($parentPageContentTypeId) {
            $objectType->setPageContentTypeId($parentPageContentTypeId);
        }
        
        $objectType->save();
                        
        return $objectTypeId;
    }
    
    public function delType($typeId)
    {        
        $db = $this->serviceManager->get('db');
        
        $result = array();
        
        $typeId = (int)$typeId;
        $objectType = $this->getType($typeId);
        
        if (false === $objectType) {
            $result['errMsg'] = 'There is no object type ' . $objectType;
            $result['success'] = false;
            return $result;
        }      
        
        if ($objectType->getIsLocked()) {
            $result['errMsg'] = 'Object type ' . $objectType . ' is locked';
            $result['success'] = false;
            return $result;
        }
        
        $descendantTypeIds = $this->getDescendantTypeIds($typeId);
        
        foreach ($descendantTypeIds as $id) {
            $sql = new Sql($db);
            $delete = $sql->delete(DB_PREF . $this->objectsTable)->where('type_id = ' . (int)$id);        
            $sql->prepareStatementForSqlObject($delete)->execute();
            
            $delete = $sql->delete(DB_PREF . $this->objectTypesTable)->where('id = ' . (int)$id);        
            $sql->prepareStatementForSqlObject($delete)->execute();
            unset($this->types[$id]);
            unset($this->allTypesList[$id]);
            unset($this->childrenTypesList[$id]);
        }
        $sql = new Sql($this->db);
        
        $delete = $sql->delete(DB_PREF . $this->objectsTable)->where('type_id = ' . (int)$typeId);        
        $sql->prepareStatementForSqlObject($delete)->execute();
        
        $delete = $sql->delete(DB_PREF . $this->objectTypesTable)->where('id = ' . (int)$typeId);        
        $sql->prepareStatementForSqlObject($delete)->execute();
        
        unset($this->types[$typeId]);
        unset($this->allTypesList[$typeId]);
        unset($this->childrenTypesList[$typeId]);    
        
        $result['success'] = true;
        return $result;
    }
        
    public function getParentTypeId($typeId)
    {        
        $db = $this->serviceManager->get('db');
        
        if (isset($this->types[$typeId])) {
            if (false === $this->types[$typeId]) {
                return false;
            }
            return $this->getType($typeId)->getParentId();
        }
        $sqlRes = $db->query('
            select parent_id 
            from ' . DB_PREF . $this->objectTypesTable . ' 
            where id = ? 
            limit 1', array($typeId))->toArray();
                
        if (empty($sqlRes)) {
            return false;
        }
        else {
            return $sqlRes[0]['parent_id'];
        }
    }
    
    public function getChildrenTypeIds($parentId)
    {                
        $db = $this->serviceManager->get('db');
        
        $query = '
            SELECT id 
            FROM ' . DB_PREF . $this->objectTypesTable . ' 
            WHERE parent_id = ?';

        $sqlRes = $db->query($query, array($parentId))->toArray();

        $ids = array();
        foreach ($sqlRes as $row) {
            $ids[] = $row['id'];
        }
        return $ids;
    }
    
    public function getDescendantTypeIds($parentId)
    {        
        $ids = $this->getChildrenTypeIds($parentId);
        
        foreach ($ids as $id) {
            $tmp = $this->getDescendantTypeIds($id);
            
            $ids = array_merge($ids, $tmp);
        }
        
        return $ids;
    }
    
    public function getGuidesList($parentId = null)
    {
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        $query = '
            select * 
            from ' . DB_PREF . $this->objectTypesTable . ' 
            where is_guidable = 1';
        
        if (null !== $parentId) {
            $query .= ' and parent_id = ' . (int)$parentId;
        }
        
        $sqlRes = $db->query($query, array());
        
        $guides = array();
        foreach ($sqlRes as $row) {
            $guides[$row['id']] = $translator->translateI18n($row['name']);
        }
        
        return $guides;
    }
    
    public function getGuidesData($parentId = null)
    {
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        $query = '
            select * 
            from ' . DB_PREF . $this->objectTypesTable . ' 
            where is_guidable = 1';
        
        if (null !== $parentId) {
            $query .= ' and parent_id = ' . (int)$parentId;
        }
        
        $sqlRes = $db->query($query, array())->toArray();
        
        $guides = array();
        foreach ($sqlRes as $row) {
            $row['name'] = $translator->translateI18n($row['name']);
            $guides[$row['id']] = $row;
        }
        
        return $guides;
    }
    
    public function getTypesListByPageTypeId($pageTypeId)
    {
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        $sqlRes = $db->query('
            select id, name 
            from ' . DB_PREF . $this->objectTypesTable . ' 
            where page_type_id = ?', array($pageTypeId))->toArray();
        
        $types = array();        
        foreach ($sqlRes as $row) {
            $types[$row['id']] = $translator->translateI18n($row['name']);
        }
        
        return $types;
    }
    
    public function getTypesListByPageContentTypeId($pageContentTypeId)
    {
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        $sqlRes = $db->query('
            select id, name 
            from ' . DB_PREF . $this->objectTypesTable . ' 
            where page_content_type_id = ?', array($pageContentTypeId))->toArray();
        
        $types = array();
        
        foreach ($sqlRes as $row) {
            $types[$row['id']] = $translator->translateI18n($row['name']);
        }
        
        return $types;
    }
    
    public function getChildrenTypesList($parentId)
    {      
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        if (!isset($this->childrenTypes[$parentId])) {
            $sqlRes = $db->query('
                SELECT t1.*,
                    (SELECT count(t2.id) 
                    FROM ' . DB_PREF . $this->objectTypesTable . ' t2 
                    WHERE t2.parent_id = t1.id) AS children_cnt
                FROM ' . DB_PREF . $this->objectTypesTable . ' t1
                WHERE t1.parent_id = ?', array($parentId))->toArray();
            
            $this->childrenTypes[$parentId] = array();
            foreach ($sqlRes as $row) {
                $row['name'] = $translator->translateI18n($row['name']);
                $this->childrenTypes[$parentId][$row['id']] = $row;
            }
        }
        return $this->childrenTypes[$parentId];
    }
    
    public function getAllTypesList()
    {
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        if (null === $this->allTypes) {
            $sqlRes = $db->query('
                SELECT t1.*, 
                    (SELECT count(t2.id) 
                    FROM ' . DB_PREF . $this->objectTypesTable . ' t2 
                    WHERE t2.parent_id = t1.id) AS children_cnt
                FROM ' . DB_PREF . $this->objectTypesTable . ' t1', array())->toArray();
            
            $this->allTypes = array();
            foreach ($sqlRes as $row) {
                $row['name'] = $translator->translateI18n($row['name']);
                $this->allTypes[$row['id']] = $row;
            }
        }
        return $this->allTypes;
    }
    
}