<?php

namespace Users\Method;

use Pages\Entity\FeContentMethod;
use Users\Model\Users;

class LoginForm extends FeContentMethod
{
    protected $usersModel;
    
    public function init()
    {
        $sm = $this->serviceLocator->getServiceLocator();
        $this->usersModel = new Users($sm);
        $this->configManager = $sm->get('configManager');
    }


    public function main()
    {        
        $result = array();
        
        $result['formConfig'] = $this->usersModel->getLoginFormConfig();
        $result['formValues'] = array();     

        $fm = $this->flashMessenger()->setNamespace('users-login-form')->getMessages();
        if (isset($fm[0])) {
            $result['formMsg'] = array(
                'identity' => array($fm[0]),
            );
        }          
        $result['enableRegistration'] = (bool)$this->configManager->get('users', 'allow_registration');
        
        return $result;
    }    
}