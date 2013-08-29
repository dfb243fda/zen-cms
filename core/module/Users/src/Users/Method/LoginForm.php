<?php

namespace Users\Method;

use Pages\AbstractMethod\FeContentMethod;
use Users\Model\Users;

class LoginForm extends FeContentMethod
{
    public function main()
    {   
        $usersModel = new Users($this->serviceLocator);
        $configManager = $this->serviceLocator->get('configManager');
        
        $result = array();
        
        $result['formConfig'] = $usersModel->getLoginFormConfig();
        $result['formValues'] = array();     

        $fm = $this->flashMessenger()->setNamespace('users-login-form')->getMessages();
        if (isset($fm[0])) {
            $result['formMsg'] = array(
                'identity' => array($fm[0]),
            );
        }          
        $result['enableRegistration'] = (bool)$configManager->get('users', 'allow_registration');
        
        return $result;
    }    
}