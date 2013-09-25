<?php

namespace Users\Method;

use App\Method\AbstractMethod;

class EditUser extends AbstractMethod
{    
    public function main()
    {
        $result = array();
        
        $userId = $this->params()->fromRoute('id');
        if (null === $userId) {
            $result['errMsg'] = 'user id is undefined';
            return $result;
        }
        $userId = (int)$userId;
        
        $usersCollection = $this->serviceLocator->get('Users\Collection\Users');
        $userEntity = $usersCollection->getUserById($userId);
        $usersService = $this->serviceLocator->get('Users\Service\Users');
        
        if (!$userEntity) {
            $result['errMsg'] = 'user ' . $userId . ' not found';
            return $result;
        }
        
        $request = $this->serviceLocator->get('request');
        
        if ($this->params()->fromRoute('objectTypeId') !== null) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $usersService->getTypeIds())) {
                $result['errMsg'] = 'object type ' . $objectTypeId . ' is not user';
                return $result;
            }            
            $userEntity->setObjectTypeId($objectTypeId);
        }                
        
        if ($request->isPost()) {
            $form = $userEntity->getForm(false); 
            
            $form->setData($this->params()->fromPost());
            
            if ($form->isValid()) {
                if ($userEntity->editUser($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Пользователь успешно изменен');
                        return $this->redirect()->refresh();
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Пользователь успешно изменен',
                    );         
                } else {
                    $result['errMsg'] = 'При изменении пользователя произошли ошибки';
                    $result['success'] = false;
                }
            } else {
                $result['success'] = false;
            }            
        } else {
            $form = $userEntity->getForm(true); 
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Users/user_form.phtml',
            'data' => array(
                'form' => $form,
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