<?php

namespace Installer\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\Form\Factory;
use Zend\Session\Container as SessionContainer;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Exception\RuntimeException;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Validator\AbstractValidator;

class InstallController extends AbstractActionController
{
    protected $installerModel;
    
    protected $translator;
    
    protected $installSession;
    
    protected function init()
    {       
        $this->installerModel = $this->serviceLocator->get('Installer\Model\Installer');
        $this->translator = $this->serviceLocator->get('translator');
        
        $this->installSession = new SessionContainer('installer');
        
        AbstractValidator::setDefaultTranslator($this->translator);
        
        if (isset($this->installSession->step1['language'])) {
            $this->translator->setLocale($this->installSession->step1['language']);
        }
        
        $appConfig = $this->serviceLocator->get('ApplicationConfig');
                
        $phpSettings = $appConfig['phpSettings'];
        foreach($phpSettings as $key => $value) {
            ini_set($key, $value);
        }        
    }
    
    public function step1Action()
    {
        $this->init();
        
        if (file_exists(APPLICATION_PATH . '/config/application.config.php')) {            
            $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
            $this->response->setContent('Installer system already installed');
            return $this->response;
        }
        
        $installSession = $this->installSession;
        if (isset($installSession->currentStep)) {
            $currentStep = $installSession->currentStep;
        } else {
            $currentStep = 1;
        }
        
        if (1 !== $currentStep) {
            $this->redirect()->toRoute('install', array(
                'action' => 'step' . $currentStep,
            ));
            return $this->response;
        }
        
        $this->installerModel->copyInstallerResources();
        
        $formConfig = $this->installerModel->getLanguageFormConfig();        
        $formMsg = array();
        
        if ($this->request->isPost()) {
            $formValues = $this->request->getPost();
            
            $factory = new Factory($this->serviceLocator->get('FormElementManager'));

            $form = $factory->createForm($formConfig);         
            $form->setData($formValues);
            
            if ($form->isValid()) {
                $formValues = $form->getData();
                
                $installSession->step1 = array(
                    'language' => $formValues['language'],
                );
                $installSession->currentStep = 2; 
                $this->redirect()->toRoute('install', array(
                    'action' => 'step2',
                ));                
                return $this->response;
            } else {
                $formMsg = $form->getMessages();
            }            
        } else {
            if (isset($installSession->step2)) {
                $formValues = $installSession->step2;
            } else {
                $formValues = $this->installerModel->getLanguageFormDefaultValues();
            }            
        }
        
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
        
        return array(
            'formConfig' => $formConfig,
            'formValues' => $formValues,
            'formMsg' => $formMsg,
        );
    }
    
    public function step2Action()
    {
        $this->init();
        
        if (file_exists(APPLICATION_PATH . '/config/application.config.php')) {            
            $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
            $this->response->setContent('Installer system already installed');
            return $this->response;
        }
                
        $installSession = $this->installSession;
        if (isset($installSession->currentStep)) {
            $currentStep = $installSession->currentStep;
        } else {
            $currentStep = 1;
        }
        
        if (2 !== $currentStep) {
            $this->redirect()->toRoute('install', array(
                'action' => 'step' . $currentStep,
            ));
            return $this->response;
        }
        
        $formConfig = $this->installerModel->getDbFormConfig();        
        $formMsg = array();
        $msg = array();
        
        if ($this->request->isPost()) {
            $formValues = $this->request->getPost();
            
            $factory = new Factory($this->serviceLocator->get('FormElementManager'));

            $form = $factory->createForm($formConfig);         
            $form->setData($formValues);
            
            if ($form->isValid()) {
                $formValues = $form->getData();
                
                $installSession->step2 = array(
                    'dbname' => $formValues['dbname'],
                    'dbuser' => $formValues['dbuser'],
                    'dbpass' => $formValues['dbpass'],
                    'dbaddr' => $formValues['dbaddr'],
                    'dbpref' => $formValues['dbpref'],
                );
                
                $config = $this->serviceLocator->get('ApplicationConfig');
                $config['db']['dsn'] = 'mysql:dbname=' . $formValues['dbname'] . ';host=' . $formValues['dbaddr'];
                $config['db']['username'] = $formValues['dbuser'];
                $config['db']['password'] = $formValues['dbpass'];
                
                $adapter = new Adapter($config['db']);
                
                $success = true;
                try {
                    $adapter->getDriver()->getConnection()->connect();
                } catch(RuntimeException $e) {
                    $msg[] = $this->translator->translate('Installer There is error while connecting to db') . ': ' . $e->getMessage();
                    $success = false;
                }
                
                if ($success) {
                    $installSession->currentStep = 3;                    
                    $this->redirect()->toRoute('install', array(
                        'action' => 'step3',
                    ));                
                    return $this->response;
                }                      
            } else {
                $formMsg = $form->getMessages();
            }
        } else {
            if (isset($installSession->step2)) {
                $formValues = $installSession->step2;
            } else {
                $formValues = $this->installerModel->getDbFormDefaultValues();
            }            
        }
        
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
        
        return array(
            'formConfig' => $formConfig,
            'formValues' => $formValues,
            'formMsg' => $formMsg,
            'msg' => $msg,
        );
    }
    
    public function step3Action()
    {
        $this->init();   
        
        if (file_exists(APPLICATION_PATH . '/config/application.config.php')) {            
            $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
            $this->response->setContent('Installer system already installed');
            return $this->response;
        }
        
        $installSession = $this->installSession;
        if (isset($installSession->currentStep)) {
            $currentStep = $installSession->currentStep;
        } else {
            $currentStep = 1;
        }
        
        if (3 !== $currentStep) {
            $this->redirect()->toRoute('install', array(
                'action' => 'step' . $currentStep,
            ));
            return $this->response;
        }
        
        $checkResult = $this->installerModel->getCheckServerResult();
        
        if ($this->request->isPost() && $checkResult['success']) {
            $installSession->currentStep = 4;                    
            $this->redirect()->toRoute('install', array(
                'action' => 'step4',
            ));                
            return $this->response;
        }
        
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
        
        return array(
            'checkResult' => $checkResult,
        );
    }
    
    public function step4Action()
    {
        $this->init();        
        
        if (file_exists(APPLICATION_PATH . '/config/application.config.php')) {            
            $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
            $this->response->setContent('Installer system already installed');
            return $this->response;
        }
        
        $installSession = $this->installSession;
        if (isset($installSession->currentStep)) {
            $currentStep = $installSession->currentStep;
        } else {
            $currentStep = 1;
        }
        
        if (4 !== $currentStep) {
            $this->redirect()->toRoute('install', array(
                'action' => 'step' . $currentStep,
            ));
            return $this->response;
        }
        
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                
        if ($this->request->isPost()) {            
            if ($this->installerModel->installCms()) {
                $installSession->currentStep = 5;                    
                $this->redirect()->toRoute('install', array(
                    'action' => 'step5',
                ));                
                return $this->response;
            } else {
                return array(
                    'installResult' => false
                );
            }
        }
        
        return array();
    }
    
    public function step5Action()
    {
        $this->init();        
        
        if (file_exists(APPLICATION_PATH . '/config/application.config.php')) {            
            $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
            $this->response->setContent('Installer system already installed');
            return $this->response;
        }
        
        $installSession = $this->installSession;
        if (isset($installSession->currentStep)) {
            $currentStep = $installSession->currentStep;
        } else {
            $currentStep = 1;
        }
        
        if (5 !== $currentStep) {
            $this->redirect()->toRoute('install', array(
                'action' => 'step' . $currentStep,
            ));
            return $this->response;
        }
        
        $demoSites = $this->installerModel->getDemoSites();
        
        $msg = array();
        
        if ($this->request->isPost() && (null !== $this->request->getPost('demo_site'))) {
            if ('' != $this->request->getPost('demo_site')) {
                $demoSite = (string)$this->request->getPost('demo_site');
                
                $installSession->step5 = array(
                    'demoSite' => $demoSite,
                );
                                
                $installSession->currentStep = 6;                    
                $this->redirect()->toRoute('install', array(
                    'action' => 'step6',
                )); 
                return $this->response;
            } else {
                $installSession->currentStep = 6;                    
                $this->redirect()->toRoute('install', array(
                    'action' => 'step6',
                )); 
                return $this->response;
            }
        }
        
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
        
        return array(
            'msg' => $msg,
            'demoSites' => $demoSites,
        );
    }
    
    public function step6Action()
    {
        $this->init();        
        
        if (file_exists(APPLICATION_PATH . '/config/application.config.php')) {            
            $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
            $this->response->setContent('Installer system already installed');
            return $this->response;
        }        
        
        $installSession = $this->installSession;
        if (isset($installSession->currentStep)) {
            $currentStep = $installSession->currentStep;
        } else {
            $currentStep = 1;
        }
        
        if (6 !== $currentStep) {
            $this->redirect()->toRoute('install', array(
                'action' => 'step' . $currentStep,
            ));
            return $this->response;
        }
        
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
        
        $formConfig = $this->installerModel->getAccessFormConfig();
        
        $redirectUrl = $this->url()->fromRoute('install', array(
            'action' => 'step6',
        ));
        
        $prg = $this->prg($redirectUrl, true);
        
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return array(
                'formConfig' => $formConfig,
                'formValues' => array(),
            );
        }
        
        $post = $prg;
        
        $factory = new Factory($this->serviceLocator->get('FormElementManager'));
        $form = $factory->createForm($formConfig);         
        $form->setData($post);
        $form->prepare();

        
        if ($form->isValid()) {
            $formValues = $form->getData();
            
            $installSession->step6 = array(
                'email' => $formValues['email'],
                'password' => $formValues['password'],
            );
            
            $this->installerModel->createConfigFile();
            
            $installSession->currentStep = 'complete';
            
            $this->redirect()->toRoute('install', array(
                'action' => 'complete',
            )); 
            return $this->response;
        } else {
            $formValues = $form->getData();
            unset($formValues['password']);
            $formMsg = $form->getMessages();
        }        
        
        return array(            
            'formConfig' => $formConfig,
            'formMsg' => $formMsg,
            'formValues' => $formValues,
        );
    }
    
    public function completeAction()
    {
        $this->init();     
        
        $installSession = $this->installSession;
        if (isset($installSession->currentStep)) {
            $currentStep = $installSession->currentStep;
        } else {
            $currentStep = 1;
        }
        
        if ('complete' !== $currentStep) {
            $this->redirect()->toRoute('install', array(
                'action' => 'step' . $currentStep,
            ));
            return $this->response;
        }
        
        if (!file_exists(APPLICATION_PATH . '/config/application.config.php')) {            
            $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
            $this->response->setContent('Installation does not complete');
            return $this->response;
        }    
                
        $demoSite = $installSession->step5['demoSite'];
        
        $email = $installSession->step6['email'];
        $password = $installSession->step6['password'];
        
        $this->installerModel->finishInstallCms($demoSite, $email, $password);       
        $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
        
        return array();
    }
}