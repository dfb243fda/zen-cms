<?php

namespace CustomFormElements\ObjectProperty;

use Zend\Db\Sql\Sql;
use App\Object\ObjectProperty\AbstractObjectProperty;

class CkEditor extends AbstractObjectProperty
{

    protected function loadValue()
    {
        $result = array();

        $propData = $this->getPropertyData();

        if (null !== $propData) {
            foreach ($propData['text_val'] as $val) {
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
            $sql = new Sql($this->db);
            $insert = $sql->insert(DB_PREF . $this->objectContentTable)->values(array(
                'object_id'   => $this->objectId,
                'field_id'    => $this->fieldId,
                'text_val' => $value,
            ));
            $sql->prepareStatementForSqlObject($insert)->execute();  
        }
    }

}