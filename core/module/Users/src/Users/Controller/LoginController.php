<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\Storage\Session;

class LoginController extends AbstractActionController
{
    const ROUTE_LOGIN = 'login';
    
    public function indexAction()
    {
        $request = $this->getRequest();
        $translator = $this->serviceLocator->get('translator');
        $configManager = $this->serviceLocator->get('configManager');
        $systemInfoService = $this->serviceLocator->get('App\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('App\Service\Errors');
        $rendererStrategy = $this->serviceLocator->get('App\View\RendererStrategy');        
        $rendererStrategyOptions = $this->serviceLocator->get('Users\View\LoginRendererStrategyOptions');    
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
        
        if ($request->isPost()) {   
            $formElementManager = $this->serviceLocator->get('FormElementManager');
        
            $form = $formElementManager->get('Users\Form\LoginForm');
            $form->setData($request->getPost());
            
            $authService = $this->serviceLocator->get('Users\Service\UserAuthentication');
            
            if ($form->isValid() && $authService->authenticate()) {
                if ($redirect) {
                    return $this->redirect()->toUrl($redirect);
                }
                return $this->redirect()->toRoute($usersConfig['loginRedirectRoute']);
            } else {
                $this->flashMessenger()->setNamespace('users-login-form')->addMessage($translator->translate('Users:Failed login msg'));
                return $this->redirect()->toRoute(static::ROUTE_LOGIN, array(), array('query' => $redirect ? array('redirect' => $redirect) : array()));
            }
        } else {
            $loginForm = $formElementManager->get('Users\Form\LoginForm');
            
            $fm = $this->flashMessenger()->setNamespace('users-login-form')->getMessages();
            if (isset($fm[0])) {
                $loginForm->get('identity')->setMessages(array($fm[0]));
            }      
            
            $resultArray['form'] = $loginForm;
            $resultArray['redirect'] = $redirect;
            $resultArray['enableRegistration'] = (bool)$configManager->get('users', 'allow_registration');
            $resultArray['enableLoginza'] = (bool)$configManager->get('loginza', 'allow_loginza');
        }
        
        $resultArray['systemInfo'] = $systemInfoService->getSystemInfo();
        
        $errors = $errorsService->getErrors();
        if (!empty($errors)) {
            $resultArray['errors'] = $errors;
        }
        
        
        $rendererStrategy->setFormat($rendererStrategyOptions->getFormat())
                         ->setTarget($this)
                         ->setRendererStrategies($rendererStrategyOptions->getRendererStrategies())
                         ->setResultComposers($rendererStrategyOptions->getResultComposers());
        
        $rendererStrategy->registerStrategy();        
                      
        $eventManager->trigger('prepare_output', $this, array($resultArray));
                        
        return $rendererStrategy->getResult($resultArray);
    }
    
    public function loginzaAction()
    {
        if (null === $this->request->getPost('token')) {
            $output = 'token does not transferred';
            
            $this->response->setContent($output);
            return $this->response;
        }
        
        $config = $this->serviceLocator->get('config');
        $usersConfig = $config['Users'];   
        
        if ($usersConfig['useRedirectParameterIfPresent'] && $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect'))) {
            $redirect = $this->params()->fromQuery('redirect');
        } else {
            $redirect = false;
        }
        
        $token = (string)$this->request->getPost('token');
        $widgetId = '58015';
        $signature = md5($token . 'a7032b197c14a960e90e44b4d698f5ae');
        
  //      $url = "http://loginza.ru/api/authinfo?token=$token&id=$widgetId&sig=$signature";
        $url = "http://loginza.ru/api/authinfo?token=$token";
                
        $content = file_get_contents($url);
        
        $contentArr = json_decode($content, true);
        
        if (is_array($contentArr)) {
            if (isset($contentArr['identity']) && isset($contentArr['provider'])) {
                $authService = $this->serviceLocator->get('Users\Service\UserAuthentication');                
                $authService->setAdaptersType('loginza');
                
                if ($authService->authenticate($contentArr)) {
                    if ($redirect) {
                        return $this->redirect()->toUrl($redirect);
                    }
                    return $this->redirect()->toRoute($usersConfig['loginRedirectRoute']);
                } else {
                    $adapter = $authService->getAdapter();
                    $event = $adapter->getEvent();
                    
                    if (AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND == $event->getCode()) {
                        $storage = new Session(get_class());
                        
                        $storage->write($event->getData());
                        
                        $confirmUrl = $this->url()->fromRoute('login', array(
                            'action' => 'loginza_confirm',
                        ));
                        
                        $output = 'Вы ещё не зарегистрированы, хотите зарегистрироваться? 
                            <form type="GET" action="' .$confirmUrl . '">
                            <input type="submit" value="Зарегистрироваться">
                            </form>
                            <a href="' . $this->url()->fromRoute('login') . '">Отменить</a>
                        ';
                    } else {
                        $output = 'errors on registration';
                    }
                }
            } elseif (isset($contentArr['error_message'])) {
                $output = (string)$contentArr['error_message'];
            } else {
                $output = 'loginza bad response';
            }
        } else {
            $output = 'loginza bad response';
        }
        
        $this->response->setContent($output);
        return $this->response;
    }
    
    public function loginzaConfirmAction()
    {
        $storage = new Session(get_class());
                   
        if ($storage->isEmpty()) {
            $output = 'wrong parameters transferred';
            
            $this->response->setContent($output);
            return $this->response;
        }
                
        $data = $storage->read();
        $storage->clear();
        
        $config = $this->serviceLocator->get('config');
        $usersConfig = $config['Users'];   
        
        if ($usersConfig['useRedirectParameterIfPresent'] && $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect'))) {
            $redirect = $this->params()->fromQuery('redirect');
        } else {
            $redirect = false;
        }
        
        if (!isset($data['identity']) || !isset($data['provider'])) {
            $output = 'wrong parameters transferred';
            
            $this->response->setContent($output);
            return $this->response;
        }
        
        $configManager = $this->serviceLocator->get('configManager');
        $objectTypeId = $configManager->get('users', 'new_user_object_type');
        
        $registrationService = $this->serviceLocator->get('Users\Service\UserRegistration');
        $registrationService->setObjectTypeId($objectTypeId);

        $userData = array(
            'loginza_id' => $data['identity'],
            'loginza_provider' => $data['provider'],
            'loginza_data' => json_encode($data),
        );
        
        if (isset($data['email'])) {
            $userData['email'] = $data['email'];
        }
        if (isset($data['name'])) {
            if (isset($data['name']['full_name'])) {
                $userData['display_name'] = $data['name']['full_name'];
            } else {
                $parts = array();
                if (isset($data['name']['first_name'])) {
                    $parts[] = $data['name']['first_name'];
                }
                if (isset($data['name']['last_name'])) {
                    $parts[] = $data['name']['last_name'];
                }
                $userData['display_name'] = implode(' ', $parts);
            }
            
        }
        
        if (isset($userData['email'])) {
            $usersCollection = $this->serviceLocator->get('Users\Collection\Users');
            
            if ($usersCollection->getUserByEmail($userData['email'])) {
                $output = 'Email ' . $userData['email'] . ' already registrated in system <a href="' . $this->url()->fromRoute('login') . '">Come back</a>';
                $this->response->setContent($output);
                return $this->response;
            }
        }
        
        if ($registrationService->register($userData)) {
            if ($usersConfig['loginAfterRegistration']) {
                $authService = $this->serviceLocator->get('Users\Service\UserAuthentication');                
                $authService->setAdaptersType('loginza');                      
                $res = $authService->authenticate($data);
            }

            if ($redirect) {
                return $this->redirect()->toUrl($redirect);
            }
            return $this->redirect()->toRoute($usersConfig['registrationRedirectRoute']);
        } else {
            $output = 'При регистрации произошли ошибки';
        }
        
        
        $this->response->setContent($output);
        return $this->response;
    }
}