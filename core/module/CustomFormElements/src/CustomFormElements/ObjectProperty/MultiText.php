<?php

namespace CustomFormElements\ObjectProperty;

use Zend\Db\Sql\Sql;
use App\ObjectProperty\AbstractObjectProperty;

class MultiText extends AbstractObjectProperty
{

    protected function loadValue()
    {
        $result = array();

        $propData = $this->getPropertyData();

        if (null !== $propData) {
            foreach ($propData['varchar_val'] as $val) {
                if (null !== $val) {
                    $result[] = $val;
                }                
            }
        } 
        
        return $result;
    }

    protected function saveValue()
    {
        $this->deleteCurrentRows();
        
        foreach ($this->value as $value) {
            if ('' != $value) {
                $sql = new Sql($this->db);
                $insert = $sql->insert(DB_PREF . $this->objectContentTable)->values(array(
                    'object_id'    => $this->objectId,
                    'field_id'     => $this->fieldId,
                    'varchar_val'  => $value,
                ));  
                $sql->prepareStatementForSqlObject($insert)->execute();  
            }
        }
    }

}