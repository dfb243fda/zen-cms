<?php

namespace Users\Method;

use App\Method\AbstractMethod;
use Users\Model\Users;
use Zend\Validator\AbstractValidator;

class AddUser extends AbstractMethod
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
        
        if ($this->params()->fromRoute('objectTypeId') === null) {
            $objectTypeId = 'user-item';
        } else {
            $objectTypeId = (string)$this->params()->fromRoute('objectTypeId');
        }
        $this->usersModel->setObjectTypeId($objectTypeId);
        
        $form = $this->usersModel->getForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->usersModel->add($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Пользователь успешно добавлен');
                    $this->redirect()->toRoute('admin/method', array(
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