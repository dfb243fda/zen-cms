<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class SortField extends AbstractMethod
{    
    public function main()
    {
        $fieldsCollection = $this->serviceLocator->get('fieldsCollection');
        
        $result = array(
            'success' => 0,
        );
        
        if (null !== $this->params()->fromPost('field') &&
                null !== $this->params()->fromPost('fieldBefore') && 
                null !== $this->params()->fromPost('group') && 
                null !== $this->params()->fromPost('groupTarget')) {
            
            $fieldId = (int)$this->params()->fromPost('field');
            $fieldBeforeId = (int)$this->params()->fromPost('fieldBefore');
            $groupId = (int)$this->params()->fromPost('group');
            $groupTargetId = (int)$this->params()->fromPost('groupTarget');
                      
            $field = $fieldsCollection->getField($fieldId);
            
            if ($field->isExists()) {
                if ($field->moveFieldAfter($fieldBeforeId, $groupId, $groupTargetId)) {                    
                    $result['success'] = 1;
                }                
            }
        }
        
        return $result;
    }
}