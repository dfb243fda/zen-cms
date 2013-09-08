<?php

namespace Users\Method;

use App\Method\AbstractMethod;
use Users\Model\Users;

class EditUser extends AbstractMethod
{    
    public function main()
    {
        $usersModel = new Users($this->serviceLocator);     
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        $userId = $this->params()->fromRoute('id');
        if (null === $userId) {
            throw new \Exception('user id is undefined');
        }
        
        if ($this->params()->fromRoute('objectTypeId') !== null) {
            $objectTypeId = (string)$this->params()->fromRoute('objectTypeId');
            $usersModel->setObjectTypeId($objectTypeId);
        }        
        
        $form = $usersModel->getForm($userId);        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($request->isPost()) {
            $tmp = $usersModel->edit($userId, $this->params()->fromPost());
            if ($tmp['success']) {
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Пользователь успешно изменен');
                    return $this->redirect()->refresh();
                }

                return array(
                    'success' => true,
                    'msg' => 'Пользователь успешно изменен',
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
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
        return $result;        
    }
}