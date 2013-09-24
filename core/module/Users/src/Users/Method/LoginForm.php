<?php

namespace Users\Method;

use Pages\AbstractMethod\FeContentMethod;

class LoginForm extends FeContentMethod
{
    public function main()
    {   
        $configManager = $this->serviceLocator->get('configManager');
        $translator = $this->serviceLocator->get('translator');
        $loginzaService = $this->serviceLocator->get('Users\Service\Loginza');
        $formElementManager = $this->serviceLocator->get('FormElementManager');
        
        $result = array();
        
        $form = $formElementManager->get('Users\Form\LoginForm');
        
        if ('login' == $this->params()->fromPost('task')) { 
            $form->setData($this->params()->fromPost());
            
            $authService = $this->serviceLocator->get('Users\Service\UserAuthentication');
            
            if ($form->isValid() && $authService->authenticate()) {
                return $this->redirect()->refresh();
            } else {
                $this->flashMessenger()->setNamespace('users-login-form')->addMessage($translator->translate('Users:Failed login msg'));
                return $this->redirect()->refresh();
            }
        } else {            
            $fm = $this->flashMessenger()->setNamespace('users-login-form')->getMessages();
            if (isset($fm[0])) {
                $form->get('identity')->setMessages(array($fm[0]));
            }      
            
            $result['form'] = $form;
            $result['allowRegistration'] = (bool)$configManager->get('users', 'allow_registration');
            $result['loginza'] = $loginzaService->getLoginzaConfig();
        }
        
        return $result;
    }    
}