<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class AddGroup extends AbstractMethod
{    
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $request;
    
    protected $objectTypesModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');        
        $this->request = $this->rootServiceLocator->get('request');        
        $this->objectTypesModel = new ObjectTypesModel($this->rootServiceLocator);
    }
    
    public function main()
    {
        $result = array(
            'success' => 0,
        );
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {            
            $objectTypeId = (int)$this->params()->fromRoute('id');
            
            $tmp = $this->objectTypesModel->addGroup($objectTypeId, $this->request->getPost());
            
            if ($tmp['success']) {
                $result['msg'] = 'Группа успешно создана';
                $result['groupId'] = $tmp['groupId'];
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