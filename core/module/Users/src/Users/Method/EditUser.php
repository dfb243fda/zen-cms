<?php

namespace Users\Method;

use App\Method\AbstractMethod;
use Users\Model\Users;
use Zend\Validator\AbstractValidator;

class EditUser extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->usersModel = new Users($this->rootServiceLocator);     
        $this->request = $this->rootServiceLocator->get('request');
        AbstractValidator::setDefaultTranslator($this->rootServiceLocator->get('translator'));
    }
    
    public function main()
    {
        $result = array();
        
        $userId = $this->params()->fromRoute('id');
        if (null === $userId) {
            throw new \Exception('user id is undefined');
        }
        
        if ($this->params()->fromRoute('objectTypeId') !== null) {
            $objectTypeId = (string)$this->params()->fromRoute('objectTypeId');
            $this->usersModel->setObjectTypeId($objectTypeId);
        }        
        
        $form = $this->usersModel->getForm($userId);        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->usersModel->edit($userId, $this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Пользователь успешно изменен');
                    $this->redirect()->refresh();
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