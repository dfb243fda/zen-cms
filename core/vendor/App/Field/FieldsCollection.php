<?php

namespace App\Field;

use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FieldsCollection implements ServiceManagerAwareInterface
{    
    protected $serviceManager;
    
    protected $fields = array();
    
    protected $objectFieldsTable = 'object_fields';
        
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getField($fieldId, $fieldData = null)
    {        
        if (array_key_exists($fieldId, $this->fields)) {
            return $this->fields[$fieldId];
        }
        return $this->loadField($fieldId, $fieldData);
    }
    
    public function loadField($fieldId, $fieldData = null)
    {
        $db = $this->serviceManager->get('db');        
        
        if (null === $fieldData) {
            $sqlRes = $db->query('
                select * 
                from ' . DB_PREF . $this->objectFieldsTable . ' 
                where id = ?', array($fieldId))->toArray();
            
            if (!empty($sqlRes)) {
                $fieldData = $sqlRes[0];
            }
        }        
        
        $field = null;
        if (null !== $fieldData) {
            $field = $this->serviceManager->get('App\Field\Field');
            $field->setId($fieldId)->setFieldData($fieldData);
        }        
        $this->fields[$fieldId] = $field;
        return $field;
    }
    
    public function addField($params)
    {
        $db = $this->serviceManager->get('db');
        
        if (isset($params['guide_id']) && !$params['guide_id']) {
            $params['guide_id'] = null;
        }
        
        $sql = new Sql($db);
        
        $insert = $sql->insert(DB_PREF . $this->objectFieldsTable);
        
        $insert->values($params);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
        
        $fieldId = $db->getDriver()->getLastGeneratedValue();
        
        $this->loadField($fieldId, $params);
        
        return $fieldId;
    }
    
    public function delField($fieldId)
    {
        if (isset($this->fields[$fieldId])) {            
            unset($this->fields[$fieldId]);
        }
        
        $db = $this->serviceManager->get('db');
        
        $result = $db->query('delete from ' . DB_PREF . $this->objectFieldsTable . ' where id = ?', array($fieldId));
        
        if ($result->count() > 0) {
            return true;
        }
        return false;
    }
}