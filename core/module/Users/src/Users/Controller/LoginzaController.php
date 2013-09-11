<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\Storage\Session;

class LoginzaController extends AbstractActionController
{
    const ROUTE_LOGIN = 'login';
    
    protected $htmlTemplate;
    
    public function indexAction()
    {
        $translator = $this->serviceLocator->get('translator');
        $configManager = $this->serviceLocator->get('configManager');
        $systemInfoService = $this->serviceLocator->get('App\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('App\Service\Errors');
        $rendererStrategy = $this->serviceLocator->get('App\View\RendererStrategy');        
        $rendererStrategyOptions = $this->serviceLocator->get('Users\View\RendererStrategyOptions'); 
        $application = $this->serviceLocator->get('application');
        $eventManager = $application->getEventManager();
        $config = $this->serviceLocator->get('config');
        $usersConfig = $config['Users'];    
        
        if (null === $this->request->getPost('token')) {
            $output = 'token does not transferred';
            
            $this->response->setContent($output);
            return $this->response;
        }
        
        $resultArray = array();
        
        $config = $this->serviceLocator->get('config');
        $usersConfig = $config['Users'];   
        
        if ($usersConfig['useRedirectParameterIfPresent'] && $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect'))) {
            $redirect = $this->params()->fromQuery('redirect');
        } else {
            $redirect = false;
        }
        
        $token = (string)$this->request->getPost('token');
        $widgetId = $configManager->get('loginza', 'loginza_widget_id');
        $secret = $configManager->get('loginza', 'loginza_secret');       
        $signature = md5($token . $secret);
        
        if ($configManager->get('loginza', 'loginza_secret_is_protected')) {
            $url = "http://loginza.ru/api/authinfo?token=$token&id=$widgetId&sig=$signature";
        } else {
            $url = "http://loginza.ru/api/authinfo?token=$token";
        }
                
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
                        
                        $confirmUrl = $this->url()->fromRoute('loginza', array(
                            'action' => 'confirm_reg',
                        ));
                        
                        $redirectInput = '';
                        if ($redirect) {
                            $redirectInput = '<input type="hidden" name="redirect" value="' . $redirect . '" />';
                        }
                        
                        $resultArray['content'] =  'Вы ещё не зарегистрированы, хотите зарегистрироваться? 
                            <form type="POST" action="' .$confirmUrl . '">
                            ' . $redirectInput . '
                            <input type="submit" value="Зарегистрироваться">
                            </form>
                            <a href="' . $this->url()->fromRoute('login', array(), $redirect ? array('query' => array('redirect' => $redirect)) : array()) . '">Отменить</a>
                        ';
                    } else {
                        $resultArray['content'] = 'errors on registration';
                    }
                }
            } elseif (isset($contentArr['error_message'])) {
                $resultArray['content'] = (string)$contentArr['error_message'];
            } else {
                $resultArray['content'] = 'loginza bad response';
            }
        } else {
            $resultArray['content'] = 'loginza bad response';
        }
        
        $this->setHtmlTemplate($usersConfig['loginzaPageTemplate']);
        
        $rendererStrategy->setFormat($rendererStrategyOptions->getFormat())
                         ->setTarget($this)
                         ->setRendererStrategies($rendererStrategyOptions->getRendererStrategies())
                         ->setResultComposers($rendererStrategyOptions->getResultComposers());
        
        $rendererStrategy->registerStrategy();        
                      
        $eventManager->trigger('prepare_output', $this, array($resultArray));
                        
        return $rendererStrategy->getResult($resultArray);
    }
    
    public function confirmRegAction()
    {
        $translator = $this->serviceLocator->get('translator');
        $configManager = $this->serviceLocator->get('configManager');
        $systemInfoService = $this->serviceLocator->get('App\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('App\Service\Errors');
        $rendererStrategy = $this->serviceLocator->get('App\View\RendererStrategy');        
        $rendererStrategyOptions = $this->serviceLocator->get('Users\View\RendererStrategyOptions'); 
        $application = $this->serviceLocator->get('application');
        $eventManager = $application->getEventManager();
        $config = $this->serviceLocator->get('config');
        $usersConfig = $config['Users'];  
        
        $storage = new Session(get_class());
                   
        if ($storage->isEmpty()) {
            $output = 'wrong parameters transferred';
            
            $this->response->setContent($output);
            return $this->response;
        }
                
        $data = $storage->read();
        $storage->clear();
        
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
            $userData['display_name'] = $this->composeDisplayName($data['name']);
        }
        
        $resultArray = array();
        
        $errFlag = false;
        
        if (isset($userData['email'])) {
            $usersCollection = $this->serviceLocator->get('Users\Collection\Users');
            
            if ($usersCollection->getUserByEmail($userData['email'])) {
                $resultArray['content'] = 'Email ' . $userData['email'] . ' already registrated in system <a href="' . $this->url()->fromRoute('login', array(), $redirect ? array('query' => array('redirect' => $redirect)) : array()) . '">Come back</a>';
                $errFlag = true;
            }
        }
        
        if (!$errFlag) {
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
                $resultArray['content'] = 'При регистрации произошли ошибки';
            }
        }
        
        
        $this->setHtmlTemplate($usersConfig['loginzaPageTemplate']);
        
        $rendererStrategy->setFormat($rendererStrategyOptions->getFormat())
                         ->setTarget($this)
                         ->setRendererStrategies($rendererStrategyOptions->getRendererStrategies())
                         ->setResultComposers($rendererStrategyOptions->getResultComposers());
        
        $rendererStrategy->registerStrategy();        
                      
        $eventManager->trigger('prepare_output', $this, array($resultArray));
                        
        return $rendererStrategy->getResult($resultArray);
    }
    
    protected function composeDisplayName($data)
    {
        if (isset($data['full_name'])) {
            $displayName = $data['full_name'];
        } else {
            $parts = array();
            if (isset($data['first_name'])) {
                $parts[] = $data['first_name'];
            }
            if (isset($data['last_name'])) {
                $parts[] = $data['last_name'];
            }
            $displayName = implode(' ', $parts);
        }
        
        return $displayName;
    }
    
    public function setHtmlTemplate($template)
    {
        $this->htmlTemplate = $template;
        return $this;
    }
    
    public function getHtmlTemplate()
    {
        return $this->htmlTemplate;
    }
}