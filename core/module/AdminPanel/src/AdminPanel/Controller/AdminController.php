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
        $userDataService = $this->serviceLocator->get('Users\Service\UserData');
        $systemInfoService = $this->serviceLocator->get('App\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('App\Service\Errors');
        $rendererStrategy = $this->serviceLocator->get('App\View\RendererStrategy');        
        $rendererStrategyOptions = $this->serviceLocator->get('AdminPanel\View\RendererStrategyOptions');    
        $application = $this->serviceLocator->get('application');
        $eventManager = $application->getEventManager();
        
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
        if (is_dir(APPLICATION_PATH . '/view/theme_override/' . CURRENT_THEME)) {
            $this->serviceLocator->get('ViewTemplatePathStack')->addPath(APPLICATION_PATH . '/view/theme_override/' . CURRENT_THEME);
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
        
        $resultArray['user'] = $userDataService->getUserData();
        
        $resultArray['page'] = $pageDataService->getPageData();
        
        $resultArray['parents'] = $pageDataService->getPageParentsData($resultArray['page']);
        
        $resultArray['systemInfo'] = $systemInfoService->getSystemInfo();
        
        $errors = $errorsService->getErrors();
        if (!empty($errors)) {
            $resultArray['errors'] = $errors;
        }
                
        
        if (isset($resultArray['page']['content']) && ($resultArray['page']['content'] instanceof Response)) {
            return $resultArray['page']['content'];
        }
        if ($this->response->isRedirect()) {
            return $this->response;
        }           
        
        $rendererStrategy->setFormat($rendererStrategyOptions->getFormat())
                         ->setTarget($this)
                         ->setRendererStrategies($rendererStrategyOptions->getRendererStrategies())
                         ->setResultComposers($rendererStrategyOptions->getResultComposers());
        
        $rendererStrategy->registerStrategy();        
                      
        $eventManager->trigger('prepare_output', $this, array($resultArray));
                        
        return $rendererStrategy->getResult($resultArray);
    }
    
}