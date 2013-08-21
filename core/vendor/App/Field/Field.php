<?php

namespace App\Field;

use Zend\Db\Sql\Sql;

class Field
{
    protected $serviceManager;
    
    protected $translator;
    
    protected $objectTypesCollection;
    
    protected $fieldTypesCollection;
    
    protected $db = null;
    
    protected $id = null;
    
    protected $fieldData = null;
    
    protected $isExists = null;
    
    protected $fieldsTable = 'object_fields';
    
    protected $fieldsControllerTable = 'fields_controller';  
    
    public function __construct($options)
    {   
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        elseif (!is_array($options)) {
            throw new Zend_Exception('Invalid options provided; must be location of config file, a config object, or an array');
        }
        
        $this->setOptions($options);  
        
        if (null === $this->id) {
            throw new Zend_Exception('Field id is undefined');
        }
        
        if (null === $this->db) {
            $this->db = $this->serviceManager->get('db');
        }
        
        $this->translator = $this->serviceManager->get('translator');       
        $this->objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $this->fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');
        
        $this->init();
    }
    
    protected function init()
    {        
        $this->isExists = true;
        if ($this->fieldData === null) {
            
            $query = 'SELECT * FROM ' . DB_PREF . $this->fieldsTable . ' WHERE id = ?';

            $resultSet = $this->db->query($query, array($this->id));
            $sqlRes = $resultSet->toArray();
            
            if (empty($sqlRes)) {
                $this->isExists = false;
                $this->fieldData = array();
            } else {                
                $this->fieldData = $sqlRes[0];
            }
        }
    }
    
    public function isExists()
    {
        return $this->isExists;
    }
    
    public function save()
    {
        $fieldData = $this->fieldData;
        unset($fieldData['id']);
     
        if (isset($fieldData['guide_id']) && !$fieldData['guide_id']) {
            $fieldData['guide_id'] = null;
        }
        
        $sql = new Sql($this->db);        
        $update = $sql->update(DB_PREF . $this->fieldsTable);        
        $update->set($fieldData)->where('id = ' . (int)$this->id);        
        $sql->prepareStatementForSqlObject($update)->execute();
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
    
    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
    
    public function getId()
    {
        return $this->id;
    }
       
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }    
        
    public function getFieldData()
    {
        return $this->fieldData;
    }
    
    public function setFieldData($fieldData)
    {
        $this->fieldData = $fieldData;
        return $this;
    }
    
    public function getName()
    {
        return $this->fieldData['name'];
    }
    
    public function getTitle()
    {
        return $this->fieldData['title'];
    }
    
    public function getTip()
    {
        return $this->fieldData['tip'];
    }
    
    public function getIsRequired()
    {
        return (boolean)$this->fieldData['is_required'];
    }
    
    public function getFieldTypeId()
    {
        return $this->fieldData['field_type_id'];
    }
    
    public function getGuideId()
    {
        return $this->fieldData['guide_id'];
    }
    
    public function getInSearch()
    {
        return (boolean)$this->fieldData['in_search'];
    }
    
    public function getIsVisible()
    {
        return (boolean)$this->fieldData['is_visible'];
    }
    
    public function getFieldTypeName()
    {
        return $this->fieldTypesCollection->getFieldType($this->fieldData['field_type_id'])->getName();
    }
    
    public function getFieldTypeTitle()
    {
        return $this->fieldTypesCollection->getFieldType($this->fieldData['field_type_id'])->getTitle();
    }
    
    public function getFieldTypeIsMultiple()
    {
        return $this->fieldTypesCollection->getFieldType($this->fieldData['field_type_id'])->getIsMultiple();
    }
    
    public function getAppFormElementConfig()
    {
        $result = array(
   //         'type' => 'Zend\Form\Element\\' . ucfirst($this->getFieldTypeName()),
            'type' => $this->getFieldTypeName(),
            'name' => 'field_' . $this->getId(),
            'options' => array(
                'label' => $this->translator->translateI18n($this->getTitle()),
            ),
        );
        
        if ($this->getFieldTypeIsMultiple()) {
            $result['attributes'] = array(
                'multiple' => true,
            );
        } else {
            $result['options']['empty_option'] = '';
        }   
        
        
        if (null !== $this->getGuideId()) { 
            $sqlRes = $this->db->query('select * from ' . DB_PREF . 'objects where type_id = ?', array($this->getGuideId()));
            
            $result['options']['value_options'] = array();
            foreach ($sqlRes as $row) {
                $result['options']['value_options'][$row['id']] = $this->translator->translateI18n($row['name']);
            }
        }

        if ($this->getTip()) {
            $result['options']['description'] = $this->translator->translateI18n($this->getTip());
        }        
        
        return array(
            'spec' => $result,
            'input_filter' => array(
                'required' => (bool)$this->getIsRequired(),
            ),
        );
    }
    
    public function moveFieldAfter($fieldBeforeId, $currentGroupId, $targetGroupId)
    {
        if (0 == $fieldBeforeId) {
            $newSorting = 0;
        }
        else {
            $query = 'SELECT sorting FROM ' . DB_PREF . $this->fieldsControllerTable . ' WHERE group_id = ? AND field_id = ?';

            $resultSet = $this->db->query($query, array($targetGroupId, $fieldBeforeId));
            $sqlRes = $resultSet->toArray();            
            
            if (empty($sqlRes)) {
                return false;
            }
            $newSorting = $sqlRes[0]['sorting'] + 1;
        }
        
        $this->db->query('UPDATE ' . DB_PREF . $this->fieldsControllerTable . '
            SET sorting = (sorting + 1)
            WHERE group_id = ? AND sorting >= ?', array($targetGroupId, $newSorting));
        
        $this->db->query('UPDATE ' . DB_PREF . $this->fieldsControllerTable . '
            SET sorting = ?, group_id = ?
            WHERE group_id = ? AND field_id = ?', array($newSorting, $targetGroupId, $currentGroupId, $this->id));
        
        return true;
    }
}