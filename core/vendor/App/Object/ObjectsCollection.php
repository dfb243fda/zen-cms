<?php

namespace App\Object;

use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ObjectsCollection implements ServiceManagerAwareInterface
{
    protected $serviceManager;
        
    protected $objectTypesTable = 'object_types';
    
    protected $objectsTable = 'objects';
    
    protected $objectFieldGroupsTable = 'object_field_groups';
    
    protected $fieldsController = 'fields_controller';
    
    protected $objects = array();    
            
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getGuidedItems($id)
    {
        $db = $this->serviceManager->get('db');
        
        if (!is_numeric($id)) {
            $sqlRes = $db->query('
                select * 
                from ' . DB_PREF . $this->objectTypesTable . ' 
                where guid = ? 
                limit 1', array($id))->toArray();
            
            if (empty($sqlRes)) {
                return array();
            }
            else {
                $id = $sqlRes[0]['id'];
            }
        }
        
        $sqlRes = $db->query('
            select * 
            from ' . DB_PREF . $this->objectsTable . ' 
            where type_id = ? and is_deleted = 0', array($id))->toArray();
        
        $result = array();        
        foreach ($sqlRes as $row) {
            $result[$row['id']] = $row['name'];
        }
        
        return $result;
    }
    
    public function addObject($name, $typeId, $parentId = 0, $sorting = null)
    {
        $db = $this->serviceManager->get('db');
        
        $currentTime = time();
        
        if ($this->serviceManager->get('users_auth_service')->hasIdentity()) {
            $userId = $this->serviceManager->get('users_auth_service')->getIdentity();  
        } else {
            $userId = 0;
        }
              
        
        $sql = new Sql($db);
        $insert = $sql->insert(DB_PREF . $this->objectsTable)->values(array(
            'name' => $name, 
            'type_id' => $typeId,
            'parent_id' => $parentId,
            'sorting' => $sorting,
            'created_user' => $userId,         
            'created_time' => $currentTime,
            'modified_time' => $currentTime,
            'is_active' => 1,
            'is_deleted' => 0,
        ));
        $sql->prepareStatementForSqlObject($insert)->execute();  
        
        $objectId = $db->getDriver()->getLastGeneratedValue();
        
        return $objectId;
    }
    
    
    public function delObject($objectId, $flagAsDelete = true)
    {
        $db = $this->serviceManager->get('db');
        
        if ($this->isExists($objectId)) {
            $childrenObjectIds = $this->getChildrenObjectIds($objectId);
            
            if ($flagAsDelete) {
                $db->query('
                    update ' . DB_PREF . $this->objectsTable . ' 
                    set is_deleted=1 
                    where id = ?', array($objectId));
            } else {
                $db->query('
                    delete from ' . DB_PREF . $this->objectsTable . ' 
                    where id = ?', array($objectId));
            }
            
            if (array_key_exists($objectId, $this->objects)) {
                unset($this->objects[$objectId]);
            }           
            
            foreach ($childrenObjectIds as $id) {
                $this->delObject($id, $flagAsDelete);
            }
            
            return true;
        } else {
            return false;
        }
    }
    
    public function getChildrenObjectIds($parentId)
    {                
        $db = $this->serviceManager->get('db');
        
        $query = '
            SELECT id 
            FROM ' . DB_PREF . $this->objectsTable . ' 
            WHERE parent_id = ?';

        $sqlRes = $db->query($query, array($parentId))->toArray();

        $ids = array();
        foreach ($sqlRes as $row) {
            $ids[] = $row['id'];
        }
        return $ids;
    }
        
    public function getObject($objectId, $objectData = null)
    {        
        $db = $this->serviceManager->get('db');
        
        if (array_key_exists($objectId, $this->objects)) {
            return $this->objects[$objectId];
        }
        
        if (null === $objectData) {
            $sqlRes = $db->query('
                select * 
                from ' . DB_PREF . $this->objectsTable . ' 
                where id = ?', array($objectId))->toArray();
            
            if (!empty($sqlRes)) {
                $objectData = $sqlRes[0];
            }
        }        
        
        $object = null;
        if (null !== $objectData) {
            $object = $this->serviceManager->get('App\Object\Object');
            $object->setId($objectId)->setObjectData($objectData)->init();
        }        
        $this->objects[$objectId] = $object;
        
        return $object;
    }
    
    public function isExists($objectId)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            SELECT count(id) AS cnt 
            FROM ' . DB_PREF . $this->objectsTable . ' 
            WHERE id = ?', array($objectId))->toArray();
        
        return ($sqlRes[0]['cnt'] > 0);
    }
}