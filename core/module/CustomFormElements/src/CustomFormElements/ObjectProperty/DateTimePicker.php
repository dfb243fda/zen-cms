<?php

namespace CustomFormElements\ObjectProperty;

use Zend\Db\Sql\Sql;
use App\Object\ObjectProperty\AbstractObjectProperty;

class DateTimePicker extends AbstractObjectProperty
{

    protected function loadValue()
    {
        $result = array();
        
        $propData = $this->getPropertyData();

        if (null !== $propData) {
            foreach ($propData['int_val'] as $val) {
                if (null !== $val) {
                    $dateTime = new \DateTime();
                    $result[] = $dateTime->setTimestamp($val);
                }      
            }
        } 
        
        return $result;
    }

    protected function saveValue()
    {
        $this->deleteCurrentRows();
                
        foreach ($this->value as $value) { 
            if ($value) {
                if (is_object($value)) {                    
                    $value = $value->getTimestamp();
                }
                $value = (int)$value;
                
                $sql = new Sql($this->db);
                $insert = $sql->insert(DB_PREF . $this->objectContentTable)->values(array(
                    'object_id'   => $this->objectId,
                    'field_id'    => $this->fieldId,
                    'int_val'     => $value,
                )); 
                $sql->prepareStatementForSqlObject($insert)->execute();  
            }            
        }
    }

}