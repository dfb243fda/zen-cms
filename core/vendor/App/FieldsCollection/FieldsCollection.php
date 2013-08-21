<?php

namespace App\FieldsCollection;

use App\Field\Field;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FieldsCollection implements ServiceManagerAwareInterface
{    
    protected $serviceManager;
    
    protected $fields = array();
    
    protected $objectFieldsTable = 'object_fields';
    
    protected $db;
    
    protected $translator;
    
    protected $initialized = false;
    
    
    protected function init()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            
            $this->db = $this->serviceManager->get('db');
            $this->translator = $this->serviceManager->get('translator');
        }
    }
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getField($fieldId, $fieldData = null)
    {
        $this->init();
        
        if (isset($this->fields[$fieldId])) {
            return $this->fields[$fieldId];
        } else {
            return $this->loadField($fieldId, $fieldData);
        }
    }
    
    public function loadField($fieldId, $fieldData = null)
    {
        $this->init();
        
        $fieldConfig = array(
            'serviceManager' => $this->serviceManager,
            'id' => $fieldId,
        );
        if (null !== $fieldData) {
            $fieldConfig['fieldData'] = $fieldData;
        }
        
        $field = new Field($fieldConfig);
        $this->fields[$fieldId] = $field;
        
        return $field;
    }
    
    public function addField($params)
    {
        $this->init();
        
        if (isset($params['guide_id']) && !$params['guide_id']) {
            $params['guide_id'] = null;
        }
        
        $sql = new Sql($this->db);
        
        $insert = $sql->insert(DB_PREF . $this->objectFieldsTable);
        
        $insert->values($params);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
        
        $fieldId = $this->db->getDriver()->getLastGeneratedValue();
        
        $this->loadField($fieldId, $params);
        
        return $fieldId;
    }
    
    public function delField($fieldId)
    {
        $this->init();
        
        if (isset($this->fields[$fieldId])) {
            $sql = new Sql($this->db);
            $delete = $sql->delete(DB_PREF . $this->objectFieldsTable)->where('id = ' . (int)$fieldId);        
            $sql->prepareStatementForSqlObject($delete)->execute();
            
            unset($this->fields[$fieldId]);
            return true;
        } else {
            return false;
        }
    }
}