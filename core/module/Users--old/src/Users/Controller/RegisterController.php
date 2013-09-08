<?php

namespace Users\Controller;

use Users\Model\Users;
use Zend\View\Model\ViewModel;

use Zend\Mvc\Controller\AbstractActionController;
use Users\Options\UserControllerOptionsInterface;
use Users\Service\User as UserService;
use Zend\Form\Form;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail as SendmailTransport;


class RegisterController extends AbstractActionController
{
    protected $options;
    
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
        
        $this->usersModel->setObjectTypeId($this->configManager->get('users', 'new_user_object_type'));
    }
    
    public function indexAction()
    {
        $this->init();
        
        $options = $this->getOptions();
        
        $request = $this->getRequest();
        
        if ($this->userAuthentication()->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute($options['loginRedirectRoute']);
        } 
        
        // if registration is disabled
        if (!$this->configManager->get('users', 'allow_registration')) {
            return array('enableRegistration' => false);
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
                
        $resultArray = array(
            'enableRegistration' => (bool)$this->configManager->get('users', 'allow_registration'),
        );
        
        if ($request->isPost()) {      
            $tmpResult = $this->usersModel->register($request->getPost());
                    
            if ($tmpResult['success']) {
                $post = (array)$request->getPost();
                $configManager = $this->configManager;
                if ($configManager->get('registration', 'send_welcome_email_to_reg_users')) {
                    $subject = $configManager->get('registration', 'welcome_email_subject');
                    $text = $configManager->get('registration', 'welcome_email_text');

                    $this->sendWelcomeEmail($post, $subject, $text);
                }

                if ($options['loginAfterRegistration']) {
                    $userData = $tmpResult['userData'];
                    
                    $identityFields = $options['authIdentityFields'];
                    if (in_array('email', $identityFields)) {
                        $post['identity'] = $userData['email'];
                    } elseif (in_array('username', $identityFields)) {
                        $post['identity'] = $userData['user_name'];
                    }
                    $post['credential'] = $userData['password'];
                    $request->setPost(new Parameters($post));
                    $res = $this->usersModel->authenticate($post);
                }
                
                if ($redirect) {
                    return $this->redirect()->toUrl($redirect);
                }

                return $this->redirect()->toRoute($options['loginRedirectRoute']);
            } else {
                $resultArray['formConfig'] = $tmpResult['formConfig'];
                $resultArray['formValues'] = $tmpResult['formValues'];   
                $resultArray['formMsg'] = $tmpResult['formMsg'];   
                $resultArray['redirect'] = $redirect;
            }
        } else {
            $resultArray['formConfig'] = $this->usersModel->getRegistrationFormConfig();
            $resultArray['formValues'] = array();   
            $resultArray['redirect'] = $redirect;
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
                                
                $loginPageTemplate = $options['registerPageTemplate'];
                
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
    
    protected function sendWelcomeEmail($data, $subject, $text)
    {        
        foreach ($data as $k=>$v) {
            $v = (string)$v;
            $text = str_replace('{{' . $k . '}}', $v, $text);
        }
        
        $html = new MimePart($text);
        $html->type = "text/html";

        $bodyParts = array();
        $bodyParts[] = $html;
        
        $body = new MimeMessage();
        $body->setParts($bodyParts);        
        
        $recipient = $data['email'];
        
        $configManager = $this->getServiceLocator()->get('configManager');
        $sender = $configManager->get('system', 'admin_email');
        
        $message = new Message();
        $message->addFrom($sender)
                ->addTo($recipient)
                ->setSubject($subject);
        
        $message->setBody($body);
        $message->setEncoding("UTF-8");
        
        $transport = new SendmailTransport();
        $transport->send($message);
    }
}

