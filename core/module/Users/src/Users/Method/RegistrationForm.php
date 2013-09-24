<?php

namespace Users\Method;

use Pages\AbstractMethod\FeContentMethod;
use Zend\Stdlib\Parameters;

class RegistrationForm extends FeContentMethod
{
    public function main()
    {   
        $request = $this->serviceLocator->get('request');
        $configManager = $this->serviceLocator->get('configManager');
        $translator = $this->serviceLocator->get('translator');
        $formElementManager = $this->serviceLocator->get('FormElementManager');
        $config = $this->serviceLocator->get('config');
        $usersConfig = $config['Users'];   
                
        $allowReg = (bool)$configManager->get('users', 'allow_registration');
        
        $result = array(
            'allowRegistration' => $allowReg,
        );
        
        if ($allowReg) {
            $objectTypeId = $configManager->get('users', 'new_user_object_type');
        
            $formFactory = $this->serviceLocator->get('Users\FormFactory\RegistrationFormFactory');
            $formFactory->setObjectTypeId($objectTypeId);

            $form = $formFactory->getForm();


            if ('register' == $this->params()->fromPost('task')) {
                $form->setData($this->params()->fromPost());

                if ($form->isValid()) {
                    $registrationService = $this->serviceLocator->get('Users\Service\UserRegistration');
                    $registrationService->setObjectTypeId($objectTypeId);

                    $formData = $form->getData();

                    $userData = array();
                    foreach ($formData as $fieldsetData) {
                        foreach ($fieldsetData as $name => $val) {
                            $userData[$name] = $val;
                        }
                    }

                    if ($registrationService->register($userData)) {

                        if ($configManager->get('registration', 'send_welcome_email_to_reg_users')) {
                            $subject = $configManager->get('registration', 'welcome_email_subject');
                            $text = $configManager->get('registration', 'welcome_email_text');

                            try {
                                $registrationService->sendWelcomeEmail($userData, $subject, $text);
                            } catch (\Exception $e) { }                        
                        }

                        if ($usersConfig['loginAfterRegistration']) {
                            $identityFields = $usersConfig['authIdentityFields'];
                            if (in_array('email', $identityFields)) {
                                $post['identity'] = $userData['email'];
                            } elseif (in_array('login', $identityFields)) {
                                $post['identity'] = $userData['login'];
                            }
                            $post['credential'] = $userData['password'];
                            $request->setPost(new Parameters($post));

                            $authenticationService = $this->serviceLocator->get('Users\Service\UserAuthentication');                        
                            $res = $authenticationService->authenticate();
                        }

                        return $this->redirect()->refresh();
                    } else {
                        $result['errMsg'] = 'При регистрации произошли ошибки';
                    }
                }
            }

            $result['form'] = $form;
        }
        
        return $result;
    }    
}