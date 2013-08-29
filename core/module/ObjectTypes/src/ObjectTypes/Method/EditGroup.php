<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;
use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class EditGroup extends AbstractMethod
{    
    public function main()
    {
        $objectTypesModel = new ObjectTypesModel($this->serviceLocator);
        
        $result = array(
            'success' => 0,
        );
        if (null === $this->params()->fromRoute('id') || null === $this->params()->fromPost('name') || null === $this->params()->fromPost('title')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {
            $groupId = (int)$this->params()->fromRoute('id');
            
            $tmp = $objectTypesModel->editGroup($groupId, $this->params()->fromPost());
            
            if ($tmp['success']) {
                $result['msg'] = 'Группа успешно обновлена';
                $result['name'] = $tmp['name'];
                $result['title'] = $tmp['title'];
                $result['success'] = true;
            } else {
                $result['success'] = false;
                $form = $tmp['form'];
                $result['formMsg'] = $form->getMessages();
            }
        }
        return $result;
    }
}