<?php

namespace App\ObjectsCollection;

use App\Object\Object;

use Zend\Db\Sql\Sql;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ObjectsCollection implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $db = null;
    
    
    protected $objectTypesTable = 'object_types';
    
    protected $objectsTable = 'objects';
    
    protected $objectFieldGroupsTable = 'object_field_groups';
    
    protected $fieldsController = 'fields_controller';
    
    protected $objects = array();
    
    protected $initialized = false;
    
    
    protected function init()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            
            $this->db = $this->serviceManager->get('db');
        }
    }
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getGuidedItems($id)
    {
        $this->init();
        
        if (!is_numeric($id)) {
            $sqlRes = $this->db->query('select * from ' . DB_PREF . $this->objectTypesTable . ' where guid = ? limit 1', array($id))->toArray();
            
            if (empty($sqlRes)) {
                return array();
            }
            else {
                $id = $sqlRes[0]['id'];
            }
        }
        
        $sqlRes = $this->db->query('select * from ' . DB_PREF . $this->objectsTable . ' where type_id = ? and is_deleted = 0', array($id))->toArray();
        
        $result = array();        
        foreach ($sqlRes as $row) {
            $result[$row['id']] = $row['name'];
        }
        
        return $result;
    }
    
    public function addObject($name, $typeId, $parentId = 0, $sorting = null)
    {
        $this->init();
        
        $currentTime = time();
        
        if ($this->serviceManager->get('users_auth_service')->hasIdentity()) {
            $userId = $this->serviceManager->get('users_auth_service')->getIdentity()->getId();  
        } else {
            $userId = 0;
        }
              
        
        $sql = new Sql($this->db);
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
        
        $objectId = $this->db->getDriver()->getLastGeneratedValue();
        
        return $objectId;
    }
    
    
    public function delObject($objectId, $flagAsDelete = true)
    {
        $this->init();
        
        if ($this->isExists($objectId)) {
            if ($flagAsDelete) {
                $this->db->query('update ' . DB_PREF . $this->objectsTable . ' set is_deleted=1 where id = ?', array($objectId));
            } else {
                $this->db->query('delete from ' . DB_PREF . $this->objectsTable . ' where id = ?', array($objectId));
            }
            
            if (isset($this->objects[$objectId])) {
                unset($this->objects[$objectId]);
            }           
            return true;
        } else {
            return false;
        }
    }
        
    public function getObject($id, $objectData = null)
    {
        $this->init();
        
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        }
        
        $this->objects[$id] = new Object(array(
            'serviceManager' => $this->serviceManager,
            'id' => $id,
            'objectData' => $objectData,
        ));
        
        return $this->objects[$id];
    }
    
    public function isExists($objectId)
    {
        $this->init();
        
        $sqlRes = $this->db->query('SELECT count(id) AS cnt FROM ' . DB_PREF . $this->objectsTable . ' WHERE id = ?', array($objectId))->toArray();
        
        return ($sqlRes[0]['cnt'] > 0);
    }
}