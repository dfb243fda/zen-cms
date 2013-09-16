<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

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
                        
            $fieldsGroup = $this->serviceLocator->get('App\Field\FieldsGroup');
            $fieldsGroup->setId($groupId);
            
            if ($fieldsGroup->isExists()) {
                $result['success'] = $fieldsGroup->moveGroupAfter($groupBeforeId);  
            }
        }
        
        return $result;
    }
}