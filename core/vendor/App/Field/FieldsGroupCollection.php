<?php

namespace App\Field;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;

class FieldsGroupCollection implements ServiceManagerAwareInterface
{    
    protected $serviceManager;    
    
    protected $objectFieldGroupsTable = 'object_field_groups';
    
    protected $objectTypeId;
    
    protected $fieldGroups;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($typeId)
    {
        $this->objectTypeId = $typeId;
        return $this;
    }
    
    public function init()
    {
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $db = $this->serviceManager->get('db');
        
        $query = '
            select * 
            from ' . DB_PREF . $this->objectFieldGroupsTable . ' 
            where object_type_id = ? 
            order by sorting';
        
        $sqlRes = $db->query($query, array($this->objectTypeId))->toArray();

        $this->fieldGroups = array();
        foreach ($sqlRes as $row) {
            $fieldsGroup = $this->serviceManager->get('App\Field\FieldsGroup');
                        
            $fieldsGroup->setId($row['id'])
                        ->setGroupData($row)
                        ->loadFields();
            
            $this->fieldGroups[$row['id']] = $fieldsGroup;
        }
    }
    
    public function isFieldsGroupExists($groupId)
    {        
        return isset($this->fieldGroups[$groupId]);        
    }
    
    public function addFieldsGroup($name, $title)
    {        
        $db = $this->serviceManager->get('db');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        if (($fieldsGroup = $this->getFieldsGroupByName($name)) !== null) {
            return $fieldsGroup->getId();
        }
        
        $sqlRes = $db->query('
            SELECT MAX(sorting) AS max_sorting 
            FROM ' . DB_PREF . $this->objectFieldGroupsTable . ' 
            WHERE object_type_id = ?', array($this->objectTypeId))->toArray();
        
        $maxSorting = $sqlRes[0]['max_sorting'];
        if ($maxSorting) {
            $sorting = (int)$maxSorting + 1;
        }
        else {
            $sorting = 1;
        }
        
        $sql = new Sql($db);        
        $insert = $sql->insert(DB_PREF . $this->objectFieldGroupsTable);        
        
        $groupData = array(
            'object_type_id' => $this->objectTypeId,
            'name' => $name,
            'title' => $title,
            'is_locked' => 0,
            'sorting' => $sorting,
        );
        
        $insert->values($groupData);        
        $sql->prepareStatementForSqlObject($insert)->execute();        
        
        $fieldGroupId = $db->getDriver()->getLastGeneratedValue();
                
        $children = $objectTypesCollection->getChildrenTypeIds($this->objectTypeId);
        
        if (!empty($children)) {
            foreach ($children as $id) {
                $tmpObjectType = $objectTypesCollection->getType($id);
                
                $tmpObjectType->addFieldsGroup($name, $title);
            }            
        }
        
        $fieldsGroup = $this->serviceManager->get('App\Field\FieldsGroup');
               
        $fieldsGroup->setId($fieldGroupId)
                    ->setGroupData($groupData)
                    ->loadFields();
        
        $this->fieldGroups[$fieldGroupId] = $fieldsGroup;
        
        return $fieldGroupId;
    }    
    
    public function getFieldsGroup($groupId)
    {
        if (isset($this->fieldGroups[$groupId])) {
            return $this->fieldGroups[$groupId];
        }
        return null;
    }
    
    public function getFieldsGroupByName($name)
    {        
        $fieldGroupsList = $this->getFieldGroups();
        foreach ($fieldGroupsList as $fieldsGroup) {
            if ($fieldsGroup->getName() == $name) {
                return $fieldsGroup;
            }
        }
        return null;
    }
    
    public function getFieldGroups()
    {
        return $this->fieldGroups;
    }
    
    public function delFieldsGroup($groupId)
    {        
        $db = $this->serviceManager->get('db');
        
        $groupId = (int)$groupId;
        if (isset($this->fieldGroups[$groupId])) {  
            $db->query('
                delete from ' . DB_PREF . $this->objectFieldGroupsTable . ' 
                where id = ? 
                    and object_type_id = ?', array($groupId, $this->objectTypeId));
            
            unset($this->fieldGroups[$groupId]);
            return true;
        } else {
            return false;
        }
    }
    
}