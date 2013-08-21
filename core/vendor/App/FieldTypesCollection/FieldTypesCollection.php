<?php

namespace App\FieldTypesCollection;

use App\FieldType\FieldType;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FieldTypesCollection implements ServiceManagerAwareInterface
{    
    protected $serviceManager;
    
    protected $fieldTypesTable = 'object_field_types';
    
    protected $fieldTypes = null;
        
    protected $initialized = false;
    
    protected function init()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            
            $this->db = $this->serviceManager->get('db');

            $this->translator = $this->serviceManager->get('translator');      

            $sqlResult = $this->db->query('select * from ' . DB_PREF . $this->fieldTypesTable, array())->toArray();
            
            $this->fieldTypes = array();
            foreach ($sqlResult as $row) {
                $this->fieldTypes[$row['id']] = new FieldType(array(
                    'serviceManager' => $this->serviceManager,
                    'id' => $row['id'],
                    'typeData' => $row,
                ));
            }
        }
    }
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getFieldTypes()
    {
        $this->init();
        
        return $this->fieldTypes;
    }
    
    public function getFieldType($typeId)
    {
        $this->init();
        
        if (isset($this->fieldTypes[$typeId])) {
            return $this->fieldTypes[$typeId];
        }
        return null;
    }
    
    public function getFieldTypeByDataType($dataType, $isMultiple = false)
    {
        $this->init();
        
        $fieldType = null;
        
        foreach ($this->fieldTypes as $v) {
            if ($v->getName() == $dataType && $v->getIsMultiple() == $isMultiple) {
                $fieldType = $v;
            }
        }
        
        return $fieldType;
    }
    
    public function getFieldTypeIdByDataType($dataType, $isMultiple = false)
    {
        $this->init();
        
        $fieldTypeId = null;
        
        foreach ($this->fieldTypes as $k=>$v) {
            if ($v->getName() == $dataType && $v->getIsMultiple() == $isMultiple) {
                $fieldTypeId = $k;
            }
        }
        
        return $fieldTypeId;
    }
    
    
}