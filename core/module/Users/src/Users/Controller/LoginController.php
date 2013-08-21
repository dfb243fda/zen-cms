<?php

namespace Users\Controller;

use Zend\View\Model\ViewModel;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Form;
use Users\Options\UserControllerOptionsInterface;

use Users\Model\Users;
use Zend\Form\Factory;

class LoginController extends AbstractActionController
{
    protected $options;
    
    const ROUTE_LOGIN        = 'login';
    
    public function init()
    {        
        $this->bugHunter = $this->serviceLocator->get('bugHunter');  
        $this->applicationConfig = $this->serviceLocator->get('ApplicationConfig');        
        $this->translator = $this->serviceLocator->get('translator'); 
        $this->configManager = $this->serviceLocator->get('configManager');
        $this->moduleManager = $this->serviceLocator->get('moduleManager');           
        $this->config = $this->serviceLocator->get('config');
        $this->viewManager = $this->serviceLocator->get('viewManager');   
        $this->usersModel = new Users($this->serviceLocator);
    }
        
    public function indexAction()
    {
        $this->init();
        
        $options = $this->getOptions();
        
        $request = $this->getRequest();
        
        if ($this->userAuthentication()->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute($options['loginRedirectRoute']);
        } 
        
        if ($this->request->isXmlHttpRequest()) {
            $defaultFormat = 'json';
        }
        else {
            $defaultFormat = 'html';
        }
        
        if (null === $this->params()->fromRoute('format')) {
            $format = $defaultFormat;
        }
        else {
            $format = (string)$this->params()->fromRoute('format');
            if (!in_array($format, array('json', 'html', 'xml', 'var_dump', 'print_r'))) {
                $format = $defaultFormat;
            }
        }   
        
        if ($options['useRedirectParameterIfPresent'] && $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect'))) {
            $redirect = $this->params()->fromQuery('redirect');
        } else {
            $redirect = false;
        }
                
        $resultArray = array();
        
        if ($request->isPost()) {      
            $tmpResult = $this->usersModel->authenticate($request->getPost());
                        
            if ($tmpResult) {
                if ($redirect) {
                    return $this->redirect()->toUrl($redirect);
                }

                return $this->redirect()->toRoute($options['loginRedirectRoute']);
            } else {
                $this->flashMessenger()->setNamespace('users-login-form')->addMessage($this->translator->translate('Users:Failed login msg'));
                return $this->redirect()->toRoute(static::ROUTE_LOGIN, array(), array('query' => $redirect ? array('redirect' => $redirect) : array()));
            }
        } else {
            $resultArray['formConfig'] = $this->usersModel->getLoginFormConfig();
            $resultArray['formValues'] = array();     
            
            $fm = $this->flashMessenger()->setNamespace('users-login-form')->getMessages();
            if (isset($fm[0])) {
                $resultArray['formMsg'] = array(
                    'identity' => array($fm[0]),
                );
            }          
            $resultArray['redirect'] = $redirect;
            $resultArray['enableRegistration'] = (bool)$this->configManager->get('users', 'allow_registration');
        }
        
        
        
        $profiler = $this->getServiceLocator()->get('db')->getProfiler();
        $sqlQueriesCnt = count($profiler->getProfiles());
    
        $resultArray['systemInfo'] = array(
            'execTime' => round(microtime(true) - TIME_START, 3),
            'maxMemory' => (int)(memory_get_peak_usage() / 1024),
            'includedFilesCnt' => count(get_included_files()),
            'sqlQueriesCnt' => $sqlQueriesCnt,
        );        
        
        if ($this->bugHunter->hasLogs()) {
            $resultArray['errors'] = array();
            if ($this->isAllowed('get_errors') || true == $this->applicationConfig['show_errors_to_everybody']) {
                $resultArray['errors']['access'] = true;
            } else {
                $resultArray['errors']['access'] = false;
                $resultArray['errors']['msg'] = $this->translator->translate('There are some errors on this page, sorry for temporary inconvenience');
            }          
            $tmp = $this->bugHunter->getLogs();
            foreach ($tmp as $k=>$v) {
                unset($tmp[$k]['extra']['context']);
            }
            $resultArray['errors']['list'] = $tmp;
        }
        
        switch ($format) {
            case 'html':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                                
                $loginPageTemplate = $options['loginPageTemplate'];
                
                $view = new ViewModel();     
                
                $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                
                $view->setVariables($resultArray);                
                $view->setTemplate($loginPageTemplate);
                                
                $wrapperViewModel = $this->layout();
                
                $wrapperViewModel->content = $viewRender->render($view);
                                
                $result = $viewRender->render($wrapperViewModel);   
                
                break;

            case 'json':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'application/json; charset=utf-8'));

                if (!empty($resultArray['errors'])) {
                    foreach ($resultArray['errors'] as $k => $v) {
                        unset($resultArray['errors'][$k]['debug_backtrace']);
                        unset($resultArray['errors'][$k]['err_context']);
                    }
                }

                $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceLocator->get('viewHelperManager')));

                $result = json_encode($resultArray);

                break;
             
            case 'xml':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/xml; charset=utf-8'));

                if (!empty($resultArray['errors'])) {
                    foreach ($resultArray['errors'] as $k => $v) {
                        unset($resultArray['errors'][$k]['debug_backtrace']);
                        unset($resultArray['errors'][$k]['err_context']);
                    }
                }

                $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceLocator->get('viewHelperManager')));
                
                try {
                    $xml = \Array2XML::createXML('result', $resultArray);
                }
                catch (\Exception $e) {
                    $tmp = array('xmlError' => $e->getMessage());
                    $xml = \Array2XML::createXML('result', $tmp);
                }                

                $result = $xml->saveXML();

                break;
                
            case 'var_dump':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                
                if (!empty($resultArray['errors'])) {
                    foreach ($resultArray['errors'] as $k => $v) {
                        unset($resultArray['errors'][$k]['debug_backtrace']);
                        unset($resultArray['errors'][$k]['err_context']);
                    }
                }

                $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceLocator->get('viewHelperManager')));

                $result = '<pre>' . var_export($resultArray, true) . '</pre>';
                
                break;
                
            case 'print_r':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                
                if (!empty($resultArray['errors'])) {
                    foreach ($resultArray['errors'] as $k => $v) {
                        unset($resultArray['errors'][$k]['debug_backtrace']);
                        unset($resultArray['errors'][$k]['err_context']);
                    }
                }

                $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceLocator->get('viewHelperManager')));

                $result = '<pre>' . print_r($resultArray, true) . '</pre>';
                
                break;

            default:
                throw new \Exception('unknown return type ' . $returnType);                
        }
        
        $this->response->setContent($result); 
        return $this->response;     
    }
    
    public function getOptions()
    {
        if ($this->options === null) {
            $config = $this->getServiceLocator()->get('config');
            
            $options = $config['Users'];
            
            $this->options = $options;
        }
        return $this->options;
    }
    
    protected function getViewResources($viewHelperManager)
    {
        $headScript = $viewHelperManager->get('headScript')->getContainer()->getValue();        
        if (is_object($headScript)) {
            $headScript = array($headScript);
        }
        
        $headLink = $viewHelperManager->get('headLink')->getContainer()->getValue();
        if (is_object($headLink)) {
            $headLink = array($headLink);
        }
        
        $inlineScript = $viewHelperManager->get('inlineScript')->getContainer()->getValue();
        if (is_object($inlineScript)) {
            $inlineScript = array($inlineScript);
        }
        
        $result = array();
        if (!empty($headScript)) {
            $result['headScript'] = $headScript;
        }
        if (!empty($headLink)) {
            $result['headLink'] = $headLink;
        }
        if (!empty($inlineScript)) {
            $result['inlineScript'] = $inlineScript;
        }
        
        return $result;
    }
}



class LoginController_old extends AbstractActionController
{
    const ROUTE_CHANGEPASSWD = 'zfcuser/changepassword';
    const ROUTE_LOGIN        = 'login';
    const ROUTE_REGISTER     = 'zfcuser/register';
    const ROUTE_CHANGEEMAIL  = 'zfcuser/changeemail';
    
    protected $failedLoginMessage = 'Authentication failed. Please try again.';
    
    /**
     * @var Form
     */
    protected $loginForm;
    
    /**
     * @var UserControllerOptionsInterface
     */
    protected $options;
    
    
    public function indexAction()
    {        
        $request = $this->getRequest();
        $form    = $this->getLoginForm();

        $options = $this->getOptions();
        
        $configManager = $this->getServiceLocator()->get('configManager'); 
        
        if ($this->userAuthentication()->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute($options['loginRedirectRoute']);
        } 
        
        if ($options['useRedirectParameterIfPresent'] && $this->params()->fromQuery('redirect')) {
            $redirect = $this->params()->fromQuery('redirect');
        } else {
            $redirect = false;
        }
        
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));

        if (!$request->isPost()) {
            return array(
                'loginForm' => $form,
                'redirect'  => $redirect,
                'enableRegistration' => (bool)$configManager->get('users', 'allow_registration'),
            );
        }

        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);
            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_LOGIN).($redirect ? '?redirect='.$redirect : ''));
        }

        // clear adapters
        $this->userAuthentication()->getAuthAdapter()->resetAdapters();
        $this->userAuthentication()->getAuthService()->clearIdentity();
        
        return $this->forward()->dispatch('Users\Controller\Login', array('action' => 'authenticate'));
    }
    
    public function authenticateAction()
    {
        if ($this->userAuthentication()->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
        $adapter = $this->userAuthentication()->getAuthAdapter();
        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        $result = $adapter->prepareForAuthentication($this->getRequest());

        $options = $this->getOptions();
        
        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return $result;
        }

        $auth = $this->userAuthentication()->getAuthService()->authenticate($adapter);

        if (!$auth->isValid()) {
            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);
            $adapter->resetAdapters();
            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_LOGIN)
                . ($redirect ? '?redirect='.$redirect : ''));
        }

        if ($options['useRedirectParameterIfPresent'] && $redirect) {
            return $this->redirect()->toUrl($redirect);
        }

        return $this->redirect()->toRoute($options['loginRedirectRoute']);
    }
    
    public function getLoginForm()
    {
        if (!$this->loginForm) {
            $this->setLoginForm($this->getServiceLocator()->get('users_login_form'));
        }
        return $this->loginForm;
    }

    public function setLoginForm(Form $loginForm)
    {
        $this->loginForm = $loginForm;
        $fm = $this->flashMessenger()->setNamespace('zfcuser-login-form')->getMessages();
        if (isset($fm[0])) {
            $this->loginForm->setMessages(
                array('identity' => array($fm[0]))
            );
        }
        return $this;
    }
    
    /**
     * set options
     *
     * @param UserControllerOptionsInterface $options
     * @return UserController
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * get options
     *
     * @return UserControllerOptionsInterface
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $config = $this->getServiceLocator()->get('config');
            
            $options = $config['Users'];
            
            $this->setOptions($options);
        }
        return $this->options;
    }
}