<?php

namespace AdminPanel\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ResponseInterface as Response;


class AdminController extends AbstractActionController
{
    protected $routeLogin = 'login';
        
    public function indexAction()
    {
        $pageDataService = $this->serviceLocator->get('AdminPanel\Service\PageData');
        $userDataService = $this->serviceLocator->get('AdminPanel\Service\UserData');
        $systemInfoService = $this->serviceLocator->get('AdminPanel\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('AdminPanel\Service\Errors');
        $outputService = $this->serviceLocator->get('AdminPanel\Service\OutputRenderer');        
        
        $configManager = $this->serviceLocator->get('configManager');
        $config = $this->serviceLocator->get('config');
        $viewManager = $this->serviceLocator->get('viewManager');  
                
        if (!$this->isAllowed('admin_access')) {	
            return $this->redirect()->toRoute($this->routeLogin, array(), array('query' => array('redirect' => $this->request->getRequestUri())));
        }  
           
        $currentTheme = $configManager->get('system', 'default_be_theme');
            
        define('CURRENT_THEME', $currentTheme);
        
        if (isset($config[CURRENT_THEME]['exceptionTemplate'])) {
            $viewManager->getExceptionStrategy()->setExceptionTemplate($config[CURRENT_THEME]['exceptionTemplate']);
        }    
        if (isset($config[CURRENT_THEME]['notFoundTemplate'])) {
            $viewManager->getRouteNotFoundStrategy()->setNotFoundTemplate($config[CURRENT_THEME]['notFoundTemplate']);
        } 
        
        $pageDataService->detectModuleAndMethod();
        
        
        $resultArray = array();
        
        $resultArray['root_url'] = ROOT_URL;
        $resultArray['module'] = $pageDataService->getModule();
        
        $method = $pageDataService->getMethod();
        if (null === $method) {
            $resultArray['method'] = 'undefined';
        }
        else {
            $resultArray['method'] = $method;
        }
        
        // service page data
        $resultArray['user'] = $userDataService->getUserData();
        
        $resultArray['page'] = $pageDataService->getPageData();
        
        $resultArray['parents'] = $pageDataService->getPageParentsData($resultArray['page']);
        
        $resultArray['systemInfo'] = $systemInfoService->getSystemInfo();
        
        $errors = $errorsService->getErrors();
        $resultArray['errors'] = empty($errors) ? null : $errors;
        
        
        if (isset($resultArray['page']['content']) && ($resultArray['page']['content'] instanceof Response)) {
            return $resultArray['page']['content'];
        }
        if ($this->response->isRedirect()) {
            return $this->response;
        }    
        
        $application = $this->serviceLocator->get('application');
        $eventManager = $application->getEventManager();
        
        $eventManager->trigger('prepare_output', $this, array($resultArray));
        
        //service output
        $output = $outputService->getOutput($resultArray);
        
        $args = $eventManager->prepareArgs(array(
            'resultArray' => $resultArray,
            'output' => $output,
            'format' => $outputService->getFormat(),
        ));
        
        $eventManager->trigger('prepare_output.post', $this, $args);
        
        $this->response->setContent($args['output']); 
        return $this->response;        
    }
    
}