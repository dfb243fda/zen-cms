<?php

namespace App\Field;

use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FieldsGroup implements ServiceManagerAwareInterface
{
    protected $serviceManager;
           
    protected $groupId;
    
    protected $groupData;    
    
    protected $fieldGroupsTable = 'object_field_groups';
    
    protected $fieldsControllerTable = 'fields_controller';    
    
    protected $objectFieldsTable = 'object_fields';
    
    protected $fields = array();
        
    protected $objectsTable = 'objects';
    
    protected $objectContentTable = 'object_content';
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getId()
    {
        return $this->groupId;
    }
       
    public function setId($id)
    {
        $this->groupId = $id;
        return $this;
    }    
    
    public function getGroupData()
    {
        return $this->groupData;
    }
    
    public function setGroupData($groupData)
    {
        $this->groupData = $groupData;
        return $this;
    }
    
    public function loadFields($objectFields = null)
    {
        $db = $this->serviceManager->get('db');
        $fieldsCollection = $this->serviceManager->get('fieldsCollection');
        
        if (null === $objectFields) {
            $query = "SELECT of.* 
                FROM " . DB_PREF . $this->fieldsControllerTable . " fc, " . DB_PREF . $this->objectFieldsTable . " of 
                WHERE fc.group_id = ? AND of.id = fc.field_id
                ORDER BY fc.sorting ASC";
            
            $resultSet = $db->query($query, array($this->groupId));            
            $sqlRes = $resultSet->toArray();
            
            foreach ($sqlRes as $row) {
                $field = $fieldsCollection->getField($row['id'], $row);
                $this->fields[$row['id']] = $field;
            }
        } else {
            foreach ($objectFields as $row) {
                $field = $fieldsCollection->getField($row['id'], $row);
                $this->fields[$row['id']] = $field;
            }
        }
    }
    
     public function getFields()
    {
        return $this->fields;
    }
    
    public function getField($fieldId)
    {
        if (isset($this->fields[$fieldId])) {
            return $this->fields[$fieldId];
        }
        return null;
    }
    
    public function getFieldByName($name)
    {        
        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }
        return null;
    }
        
    public function getName()
    {
        return $this->groupData['name'];
    }
    
    public function setName($name)
    {
        $this->groupData['name'] = $name;
        return $this;
    }
    
    public function getTitle()
    {
        return $this->groupData['title'];
    }
    
    public function setTitle($title)
    {
        $this->groupData['title'] = $title;
        return $this;
    }
    
    public function getSorting()
    {
        return $this->groupData['sorting'];
    }
    
    public function getObjectTypeId()
    {
        return $this->groupData['object_type_id'];
    }
    
    public function setSorting($sorting)
    {
        $this->groupData['sorting'] = $sorting;
        return $this;
    }
    
    public function isExists()
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            select id 
            from ' . DB_PREF . $this->fieldGroupsTable . ' 
            where id = ?', array($this->groupId))->toArray();
        
        return !empty($sqlRes);
    }
    
    public function save()
    {
        $db = $this->serviceManager->get('db');
        
        $sql = new Sql($db);        
        $update = $sql->update(DB_PREF . $this->fieldGroupsTable);        
        $update->set(array(
            'name' => $this->groupData['name'],
            'title' => $this->groupData['title'],
            'object_type_id' => $this->groupData['object_type_id'],
            'is_locked' => $this->groupData['is_locked'],
            'sorting' => $this->groupData['sorting'],
        ))->where('id = ' . (int)$this->groupId);

        $sql->prepareStatementForSqlObject($update)->execute();
    }
    
    /**
     * Moves current group after group with id == $groupId
     * @param type $fieldId
     */
    public function moveGroupAfter($groupId = 0)
    {  
        $db = $this->serviceManager->get('db');
        
        if (0 == $groupId) {
            $newSorting = 0;
        } else {
            $query = 'SELECT sorting FROM ' . DB_PREF . $this->fieldGroupsTable . ' WHERE id = ? AND object_type_id = ?';

            $resultSet = $this->db->query($query, array($groupId, $this->groupData['object_type_id']));
            $sqlRes = $resultSet->toArray();

            if (empty($sqlRes)) {
                return false;
            }

            $newSorting = $sqlRes[0]['sorting'] + 1;
        }

        $db->query('UPDATE ' . DB_PREF . $this->fieldGroupsTable . '
            SET sorting = (sorting + 1)
            WHERE object_type_id = ? AND sorting >= ?', array($this->groupData['object_type_id'], $newSorting));
        
        $db->query('UPDATE ' . DB_PREF . $this->fieldGroupsTable . '
            SET sorting = ?
            WHERE id = ?', array($newSorting, $this->groupId));
        
        return true;            
    }
    
    public function attachField($fieldId)
    {
        $db = $this->serviceManager->get('db');
        $fieldsCollection = $this->serviceManager->get('fieldsCollection');
        
        if (isset($this->fields[$fieldId])) {
            return;
        }
        $field = $fieldsCollection->getField($fieldId);
        if (null === $field) {
            return;
        }        
        
        $query = 'SELECT MAX(sorting) AS max_sorting FROM ' . DB_PREF . $this->fieldsControllerTable . ' WHERE group_id = ?';

        $resultSet = $db->query($query, array($this->groupId));
        $sqlRes = $resultSet->toArray();
        $maxSorting = $sqlRes[0]['max_sorting'];
                       
        $sql = new Sql($db);        
        $insert = $sql->insert(DB_PREF . $this->fieldsControllerTable);        
        $insert->values(array(
            'field_id' => $fieldId,
            'group_id' => $this->groupId,
            'sorting' => $maxSorting + 1,
        ));
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
        
        
        $this->fields[$fieldId] = $field;
        $this->fillInContentTable($fieldId);
    }

    protected function fillInContentTable($fieldId)
    {
        $db = $this->serviceManager->get('db');
        $objectTypeId = $this->getObjectTypeId();
        
        $db->query('
            INSERT INTO ' . DB_PREF . $this->objectContentTable . '
            (object_id, field_id, int_val, varchar_val, text_val, float_val, object_rel_val, page_rel_val)
                SELECT id, ' . $fieldId . ', NULL, NULL, NULL, NULL, NULL, NULL 
                FROM ' . DB_PREF . $this->objectsTable . '
                WHERE type_id = ' . $objectTypeId);
    }

    public function detachField($fieldId)
    {
        $db = $this->serviceManager->get('db');
        $fieldsCollection = $this->serviceManager->get('fieldsCollection');
        
        if (!isset($this->fields[$fieldId])) {
            return;
        }
                
        $db->query('
            delete from ' . DB_PREF . $this->fieldsControllerTable . '
            where field_id = ? and group_id = ?', array($fieldId, $this->groupId));
                
        unset($this->fields[$fieldId]);
        
        $sqlRes = $db->query('
            SELECT COUNT(*) AS cnt 
            FROM ' . DB_PREF . $this->fieldsControllerTable . ' 
            WHERE field_id = ?', array($fieldId))->toArray();
        
        if (0 == $sqlRes[0]['cnt']) {
            $fieldsCollection->delField($fieldId);
        }
    }
}
