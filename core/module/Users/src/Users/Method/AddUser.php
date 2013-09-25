<?php

namespace Users\Method;

use App\Method\AbstractMethod;
use Users\Model\Users;

class AddUser extends AbstractMethod
{
    public function main()
    {
        $result = array();
                
        $usersCollection = $this->serviceLocator->get('Users\Collection\Users');
        $usersService = $this->serviceLocator->get('Users\Service\Users');
        $objectTypesCollection = $this->serviceLocator->get('ObjectTypesCollection');
        
        if ($this->params()->fromRoute('objectTypeId') === null) {
            $objectTypeId = $objectTypesCollection->getTypeIdByGuid($usersService->getUsersGuid()); 
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $usersService->getTypeIds())) {
                $result['errMsg'] = 'object type ' . $objectTypeId . ' is not user';
                return $result;
            }            
        }
        $usersCollection->setObjectTypeId($objectTypeId);
        
        $request = $this->serviceLocator->get('request');
                
        if ($request->isPost()) {
            $form = $usersCollection->getForm(false); 
            
            $form->setData($this->params()->fromPost());
            
            if ($form->isValid()) {
                if ($userId = $usersCollection->addUser($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Пользователь успешно добавлен');
                        return $this->redirect()->toRoute('admin/method', array(
                            'module' => 'Users',
                            'method' => 'EditUser',
                            'id' => $userId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Пользователь успешно добавлен',
                    );         
                } else {
                    $result['errMsg'] = 'При добавлении пользователя произошли ошибки';
                    $result['success'] = false;
                }
            } else {
                $result['success'] = false;
            }            
        } else {
            $form = $usersCollection->getForm(true); 
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Users/user_form.phtml',
            'data' => array(
                'form' => $form,
            ),
        );
        
        return $result;        
    }
    
    
    public function main2()
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