<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class EditGroup extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $request;
    
    protected $objectTypesModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');        
        $this->request = $this->rootServiceLocator->get('request');        
        $this->objectTypesModel = new ObjectTypesModel($this->rootServiceLocator);
    }
    
    public function main()
    {
        $result = array(
            'success' => 0,
        );
        if (null === $this->params()->fromRoute('id') || null === $this->request->getPost('name') || null === $this->request->getPost('title')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {
            $groupId = (int)$this->params()->fromRoute('id');
            
            $tmp = $this->objectTypesModel->editGroup($groupId, $this->request->getPost());
            
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