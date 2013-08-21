<?php

namespace App\ObjectProperty;

use Zend\ServiceManager\ServiceManagerAwareInterface;

use Zend\ServiceManager\ServiceManager;

abstract class AbstractObjectProperty implements ServiceManagerAwareInterface
{
    protected static $allPropertyData = array();

    protected $objectContentTable = 'object_content';

    protected $serviceManager;
    
    protected $db;
    
    protected $value;
        
    public function init()
    {
        if (null === $this->db) {
            $this->db = $this->serviceManager->get('db');
        }
        
        $this->translator = $this->serviceManager->get('translator');        
        
        $this->objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $this->fieldsCollection = $this->serviceManager->get('fieldsCollection');
        $this->fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');
        
        
        $this->field = $this->fieldsCollection->getField($this->fieldId);
        $fieldTypeId = $this->field->getFieldTypeId();
        $this->fieldType = $this->fieldTypesCollection->getFieldType($fieldTypeId);
                
        $this->value = $this->loadValue();
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

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
    
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }
    
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
        return $this;
    }
    
    protected function getPropertyData()
    {
        $allPropertyData = self::$allPropertyData;
        
        if (!isset($allPropertyData[$this->objectId])) {
            $properties = array();
            
            $sqlRes = $this->db->query('select * from ' . DB_PREF . $this->objectContentTable . ' where object_id = ?', array($this->objectId))->toArray();
                        
            foreach ($sqlRes as $row) {
                $properties[$row['field_id']]['int_val'][]        = $row['int_val'];
                $properties[$row['field_id']]['varchar_val'][]    = $row['varchar_val'];
                $properties[$row['field_id']]['text_val'][]       = $row['text_val'];
                $properties[$row['field_id']]['float_val'][]      = $row['float_val'];
                $properties[$row['field_id']]['object_rel_val'][] = $row['object_rel_val'];
                $properties[$row['field_id']]['page_rel_val'][  ] = $row['page_rel_val'];
            }
            
            $allPropertyData[$this->objectId] = $properties;
        }
        
        if (isset($allPropertyData[$this->objectId][$this->fieldId])) {
            return $allPropertyData[$this->objectId][$this->fieldId];
        } else {
            return null;
        }        
    }
    
    public function getValue()
    {
        if (!$this->fieldType->getIsMultiple()) {
            if (sizeof($this->value) > 0) {
                list($result) = $this->value;
            } else {
                $result = null;
            }
        } else {
            $result = $this->value;
        }
        return $result;
    }
    
    public function setValue($value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        
        $this->value = $value;
        return $this;
    }
    
    public function resetValue()
    {
        $this->value = array();
    }
    
    protected function deleteCurrentRows()
    {
        $this->db->query('delete from ' . DB_PREF . $this->objectContentTable . ' where object_id = ? AND field_id = ?', array($this->objectId, $this->fieldId));
    }
    
    abstract protected function loadValue();
    
    abstract protected function saveValue();
    
    public function save()
    {     
        $this->saveValue();
    }
    
        
    
}