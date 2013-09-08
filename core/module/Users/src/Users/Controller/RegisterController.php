<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\Parameters;

class RegisterController extends AbstractActionController
{
    public function indexAction()
    {
        $request = $this->getRequest();
        $translator = $this->serviceLocator->get('translator');
        $configManager = $this->serviceLocator->get('configManager');
        $systemInfoService = $this->serviceLocator->get('App\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('App\Service\Errors');
        $rendererStrategy = $this->serviceLocator->get('App\View\RendererStrategy');        
        $rendererStrategyOptions = $this->serviceLocator->get('Users\View\RegistrationRendererStrategyOptions');    
        $formElementManager = $this->serviceLocator->get('FormElementManager');
        $application = $this->serviceLocator->get('application');
        $eventManager = $application->getEventManager();
        $config = $this->serviceLocator->get('config');
        $usersConfig = $config['Users'];        
        
        
        $resultArray = array();
        
        if ($usersConfig['useRedirectParameterIfPresent'] && $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect'))) {
            $redirect = $this->params()->fromQuery('redirect');
        } else {
            $redirect = false;
        }
        
        $resultArray = array(
            'enableRegistration' => (bool)$configManager->get('users', 'allow_registration'),
        );
        
        
        $objectTypeId = $configManager->get('users', 'new_user_object_type');
        $form = $formElementManager->get('Users\Form\RegistrationForm', array('objectTypeId' => $objectTypeId));
        
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $registrationService = $this->serviceLocator->get('Users\Service\UserRegistration');
                $registrationService->setObjectTypeId($objectTypeId);
                
                $userData = $form->getData();
                
                if ($registrationService->register($userData)) {
                    if ($usersConfig['loginAfterRegistration']) {
                        $identityFields = $usersConfig['authIdentityFields'];
                        if (in_array('email', $identityFields)) {
                            $post['identity'] = $userData['common']['email'];
                        } elseif (in_array('user_name', $identityFields)) {
                            $post['identity'] = $userData['common']['user_name'];
                        }
                        $post['credential'] = $userData['common']['password'];
                        $request->setPost(new Parameters($post));
                        
                        $authenticationService = $this->serviceLocator->get('Users\Service\UserAuthentication');                        
                        $res = $authenticationService->authenticate($request);
                    }

                    if ($redirect) {
                        return $this->redirect()->toUrl($redirect);
                    }

                    return $this->redirect()->toRoute($usersConfig['registrationRedirectRoute']);
                } else {
                    $resultArray['errMsg'] = 'При регистрации произошли ошибки';
                }
            }
        }
        
        $resultArray['form'] = $form;
        
        $rendererStrategy->setFormat($rendererStrategyOptions->getFormat())
                         ->setTarget($this)
                         ->setRendererStrategies($rendererStrategyOptions->getRendererStrategies())
                         ->setResultComposers($rendererStrategyOptions->getResultComposers());
        
        $rendererStrategy->registerStrategy();        
                      
        $eventManager->trigger('prepare_output', $this, array($resultArray));
                        
        return $rendererStrategy->getResult($resultArray);
    }
        
}