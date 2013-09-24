<?php

namespace Users\Method;

use App\Method\AbstractMethod;
use Users\Model\Users;

class AddUser extends AbstractMethod
{
    
    public function main()
    {
        $usersModel = new Users($this->serviceLocator);     
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if ($this->params()->fromRoute('objectTypeId') === null) {
            $objectTypeId = 'user-item';
        } else {
            $objectTypeId = (string)$this->params()->fromRoute('objectTypeId');
        }
        $usersModel->setObjectTypeId($objectTypeId);
        
        $form = $usersModel->getForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($request->isPost()) {
            $tmp = $usersModel->add($this->params()->fromPost());
            if ($tmp['success']) {
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Пользователь успешно добавлен');
                    return $this->redirect()->toRoute('admin/method', array(
                        'module' => 'Users',
                        'method' => 'EditUser',
                        'id'     => $tmp['userId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Пользователь успешно добавлен',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
                unset($formValues['password']);
                unset($formValues['passwordVerify']);
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Users/form_view.phtml',
            'data' => array(
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg' => $formMessages,
            ),
        );
        
        return $result;        
    }
}