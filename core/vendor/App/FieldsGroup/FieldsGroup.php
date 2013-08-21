<?php

namespace App\FieldsGroup;

use Zend\Db\Sql\Sql;

class FieldsGroup
{
    protected $serviceManager;
    
    protected $db;
    
    protected $translator;
    
    protected $objectTypesCollection;
    
    protected $fieldsCollection;
    
    protected $id;
    
    protected $groupData;
    
    protected $fieldGroupsTable = 'object_field_groups';
    
    protected $isExists;
    
    protected $fieldsControllerTable = 'fields_controller';    
    
    protected $objectFieldsTable = 'object_fields';
    
    protected $fields = array();
        
    protected $objectsTable = 'objects';
    
    protected $objectContentTable = 'object_content';
    
        
    
    public function __construct($options)
    {           
        $this->setOptions($options);     
        
        if (null === $this->db) {
            $this->db = $this->serviceManager->get('db');
        }
        
        $this->translator = $this->serviceManager->get('translator');     
        $this->objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $this->fieldsCollection = $this->serviceManager->get('fieldsCollection');
        
        if (null === $this->id) {
            throw new Zend_Exception('Group id is undefined');
        }
        
        $this->init();
    }
    
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }
    
    protected function init()
    {
        $this->isExists = true;
        if (null === $this->groupData) {            
            $query = 'SELECT * FROM ' . DB_PREF . $this->fieldGroupsTable . ' WHERE id = ?';

            $resultSet = $this->db->query($query, array($this->id));
            $sqlRes = $resultSet->toArray();
            
            if (empty($sqlRes)) {
                $this->isExists = false;
                $this->groupData = array();
            }
            else {
                $this->groupData = $sqlRes[0];
            }
        }
    }
        
    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
        return $this;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($groupId)
    {
        $this->id = $groupId;
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
        if (null === $objectFields) {
            $query = "SELECT of.* 
                FROM " . DB_PREF . $this->fieldsControllerTable . " fc, " . DB_PREF . $this->objectFieldsTable . " of 
                WHERE fc.group_id = ? AND of.id = fc.field_id
                ORDER BY fc.sorting ASC";
            
            $resultSet = $this->db->query($query, array($this->id));            
            $sqlRes = $resultSet->toArray();
            
            foreach ($sqlRes as $row) {
                $field = $this->fieldsCollection->getField($row['id'], $row);
                $this->fields[$row['id']] = $field;
            }
        } else {
            foreach ($objectFields as $row) {
                $field = $this->fieldsCollection->getField($row['id'], $row);
                $this->fields[$row['id']] = $field;
            }
        }
    }
    
    public function getFields()
    {
        return $this->fields;
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
    
    public function isExists() {
        return $this->isExists;
    }
    
    public function save()
    {
        if ($this->isExists) { 
            $sql = new Sql($this->db);        
            $update = $sql->update(DB_PREF . $this->fieldGroupsTable);        
            $update->set(array(
                'name' => $this->groupData['name'],
                'title' => $this->groupData['title'],
                'object_type_id' => $this->groupData['object_type_id'],
                'is_locked' => $this->groupData['is_locked'],
                'sorting' => $this->groupData['sorting'],
            ))->where('id = ' . (int)$this->id);

            $sql->prepareStatementForSqlObject($update)->execute();
        }        
    }
    
    /**
     * Moves current group after group with id == $groupId
     * @param type $fieldId
     */
    public function moveGroupAfter($groupId = 0)
    {  
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

        $this->db->query('UPDATE ' . DB_PREF . $this->fieldGroupsTable . '
            SET sorting = (sorting + 1)
            WHERE object_type_id = ? AND sorting >= ?', array($this->groupData['object_type_id'], $newSorting));
        
        $this->db->query('UPDATE ' . DB_PREF . $this->fieldGroupsTable . '
            SET sorting = ?
            WHERE id = ?', array($newSorting, $this->id));
        
        return true;            
    }
    
    public function attachField($fieldId)
    {
        if (isset($this->fields[$fieldId])) {
            return;
        }
        
        $query = 'SELECT MAX(sorting) AS max_sorting FROM ' . DB_PREF . $this->fieldsControllerTable . ' WHERE group_id = ?';

        $resultSet = $this->db->query($query, array($this->id));
        $sqlRes = $resultSet->toArray();
        $maxSorting = $sqlRes[0]['max_sorting'];
                       
        $sql = new Sql($this->db);        
        $insert = $sql->insert(DB_PREF . $this->fieldsControllerTable);        
        $insert->values(array(
            'field_id' => $fieldId,
            'group_id' => $this->id,
            'sorting' => $maxSorting + 1,
        ));
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
        
        
        $this->fields[$fieldId] = $this->fieldsCollection->getField($fieldId);
        $this->fillInContentTable($fieldId);
    }

    protected function fillInContentTable($fieldId)
    {
        $objectTypeId = $this->getObjectTypeId();
        
        $this->db->query('INSERT INTO ' . DB_PREF . $this->objectContentTable . '
            (object_id, field_id, int_val, varchar_val, text_val, float_val, object_rel_val, page_rel_val)
            SELECT id, ' . $fieldId . ', NULL, NULL, NULL, NULL, NULL, NULL FROM ' . DB_PREF . $this->objectsTable . ' WHERE type_id = ' . $objectTypeId);
    }

    public function detachField($fieldId)
    {
        if (!isset($this->fields[$fieldId])) {
            return;
        }
                
        $sql = new Sql($this->db);
        $delete = $sql->delete(DB_PREF . $this->fieldsControllerTable)->where('field_id = ' . (int)$fieldId)->where('group_id = ' . (int)$this->id);        
        $sql->prepareStatementForSqlObject($delete)->execute();
                
        unset($this->fields[$fieldId]);
        
        $resultSet = $this->db->query('SELECT COUNT(*) AS cnt FROM ' . DB_PREF . $this->fieldsControllerTable . ' WHERE field_id = ?', array($fieldId));
        $sqlRes = $resultSet->toArray();
        
        if (0 == $sqlRes[0]['cnt']) {
            $this->fieldsCollection->delField($fieldId);
        }
    }
            
}