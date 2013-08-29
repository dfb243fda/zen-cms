<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use App\FieldsGroup\FieldsGroup;

class SortGroup extends AbstractMethod
{
    public function main()
    {
        $result = array(
            'success' => 0,
        );
        
        if (null !== $this->params()->fromPost('group') && null !== $this->params()->fromPost('groupBefore')) {
            $groupId = (int)$this->params()->fromPost('group');   
            $groupBeforeId = (int)$this->params()->fromPost('groupBefore');
            
            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $this->serviceLocator,
                'id' => $groupId,
            )); 
            
            if ($fieldsGroup->isExists()) {
                $result['success'] = (int)$fieldsGroup->moveGroupAfter($groupBeforeId);                
            }
        }
        
        return $result;
    }
}