<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LoginController extends AbstractActionController
{
    const ROUTE_LOGIN = 'login';
    
    protected $htmlTemplate;
    
    public function indexAction()
    {
        $loginzaService = $this->serviceLocator->get('Users\Service\Loginza');
        $translator = $this->serviceLocator->get('translator');
        $configManager = $this->serviceLocator->get('configManager');
        $systemInfoService = $this->serviceLocator->get('App\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('App\Service\Errors');
        $rendererStrategy = $this->serviceLocator->get('App\View\RendererStrategy');        
        $rendererStrategyOptions = $this->serviceLocator->get('Users\View\RendererStrategyOptions');    
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
        
        if ($this->request->isPost()) {   
            $formElementManager = $this->serviceLocator->get('FormElementManager');
        
            $form = $formElementManager->get('Users\Form\LoginForm');
            $form->setData($this->request->getPost());
            
            $authService = $this->serviceLocator->get('Users\Service\UserAuthentication');
            
            if ($form->isValid() && $authService->authenticate()) {
                if ($redirect) {
                    return $this->redirect()->toUrl($redirect);
                }
                return $this->redirect()->toRoute($usersConfig['loginRedirectRoute']);
            } else {
                $this->flashMessenger()->setNamespace('users-login-form')->addMessage($translator->translate('Users:Failed login msg'));
                return $this->redirect()->toRoute(static::ROUTE_LOGIN, array(), array('query' => $redirect ? array('query' => array('redirect' => $redirect)) : array()));
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
            $resultArray['loginza'] = $loginzaService->getLoginzaConfig();
        }
        
        $resultArray['systemInfo'] = $systemInfoService->getSystemInfo();
        
        $errors = $errorsService->getErrors();
        if (!empty($errors)) {
            $resultArray['errors'] = $errors;
        }
        
        $this->setHtmlTemplate($usersConfig['loginPageTemplate']);
        
        $rendererStrategy->setFormat($rendererStrategyOptions->getFormat())
                         ->setTarget($this)
                         ->setRendererStrategies($rendererStrategyOptions->getRendererStrategies())
                         ->setResultComposers($rendererStrategyOptions->getResultComposers());
        
        $rendererStrategy->registerStrategy();        
                      
        $eventManager->trigger('prepare_output', $this, array($resultArray));
                        
        return $rendererStrategy->getResult($resultArray);
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