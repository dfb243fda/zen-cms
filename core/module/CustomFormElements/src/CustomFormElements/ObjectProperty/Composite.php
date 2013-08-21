<?php

namespace CustomFormElements\ObjectProperty;

use Zend\Db\Sql\Sql;
use App\ObjectProperty\AbstractObjectProperty;

class Composite extends AbstractObjectProperty
{
    protected function parsePropData($propData, $counter)
    {
        $result = array();
        $tmp = false;
        
        foreach ($propData as $k => $v) {
            if (array_key_exists($counter, $v)) {
                $k = $this->decodeContentType($k);
                $result[$k] = $v[$counter];
                $tmp = true;
            }
        }
        
        return $tmp ? $result : false;
    }

    protected function decodeContentType($value)
    {
        if (substr($value, -4) == '_val') {
            $value = substr($value, 0, strlen($value) - 4);
        }
        return $value;
    }
    
    
    protected function loadValue()
    {
        $result = array();
        
        $propData = $this->getPropertyData();
        
        if (null !== $propData) {
            $i = 0;
            while ($parsedPropData = $this->parsePropData($propData, $i)) {                
                foreach ($parsedPropData as $k => $v) {
                    if ($k == 'varchar' || $k == 'object_rel') {
         //               $parsedPropData[$k] = $v;     
                        $result[$k][] = $v;
                    }
                }
   //             $result[] = $parsedPropData;
                $i++;
            }
        }
        
  //      print_r($result);
  //      exit();
        
        return $result;
    }

    protected function saveValue()
    {
        $this->deleteCurrentRows();           
        
        if (!empty($this->value['object_rel'])) {
            foreach ($this->value['object_rel'] as $k=>$value) {
                if (isset($this->value['varchar'][$k])) {
                    if ($value != '' || $this->value['varchar'][$k] != '') {
                        if ($value == '') {
                            $value = null;
                        }
                        
                        $sql = new Sql($this->db);
                        $insert = $sql->insert(DB_PREF . $this->objectContentTable)->values(array(
                            'object_id'      => $this->objectId,
                            'field_id'       => $this->fieldId,
                            'varchar_val'    => $this->value['varchar'][$k],
                            'object_rel_val' => $value,
                        ));   
                        
                        $sql->prepareStatementForSqlObject($insert)->execute();  
                    }
                }
            }
        }
        
    }

}