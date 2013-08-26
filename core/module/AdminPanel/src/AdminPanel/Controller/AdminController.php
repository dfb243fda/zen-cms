<?php

namespace AdminPanel\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Stdlib\ResponseInterface as Response;

class AdminController extends AbstractActionController
{    
    protected $routeLogin = 'login';
    
    protected $bugHunter;
    
    protected $applicationConfig;
    
    protected $translator;
    
    protected $configManager;
    
    protected $moduleManager;
    
    protected $config;
        
    protected $viewManager;
            
    protected function init()
    {   
        $this->bugHunter = $this->serviceLocator->get('bugHunter');  
        $this->applicationConfig = $this->serviceLocator->get('ApplicationConfig');        
        $this->translator = $this->serviceLocator->get('translator'); 
        $this->configManager = $this->serviceLocator->get('configManager');
        $this->moduleManager = $this->serviceLocator->get('moduleManager');           
        $this->config = $this->serviceLocator->get('config');
        $this->viewManager = $this->serviceLocator->get('viewManager');   
    }
    
    public function indexAction()
    {
        $this->init();        
        
        if (!$this->isAllowed('admin_access')) {	
            return $this->redirect()->toRoute($this->routeLogin, array(), array('query' => array('redirect' => $this->request->getRequestUri())));
        }     
     
//        $currentTheme = $this->_userManager->getUserData('be_theme');
//        if (!$currentTheme)
//        {
            $currentTheme = $this->configManager->get('system', 'default_be_theme');
//        }
            
        define('CURRENT_THEME', $currentTheme);
        
        if (isset($this->config[CURRENT_THEME]['exceptionTemplate'])) {
            $this->viewManager->getExceptionStrategy()->setExceptionTemplate($this->config[CURRENT_THEME]['exceptionTemplate']);
        }    
        if (isset($this->config[CURRENT_THEME]['notFoundTemplate'])) {
            $this->viewManager->getRouteNotFoundStrategy()->setNotFoundTemplate($this->config[CURRENT_THEME]['notFoundTemplate']);
        } 
        
        if ($this->request->isXmlHttpRequest()) {
            $defaultFormat = 'json_html';
        }
        else {
            $defaultFormat = 'html';
        }
        
        if (null === $this->params()->fromRoute('format')) {
            $format = $defaultFormat;
        }
        else {
            $format = (string)$this->params()->fromRoute('format');
            if (!in_array($format, array('json', 'html', 'xml', 'var_dump', 'print_r', 'json_html'))) {
                $format = $defaultFormat;
            }
        }     
        
        $resultArray = array();
        
        $mainPageModulStr = $this->configManager->get(CURRENT_THEME, 'be_main_page_modul');     
        $parts = explode(':', $mainPageModulStr);
        $mainPageModule = $parts[0];   
        $mainPageMethod = $parts[1];
        
        if (null === $this->params()->fromRoute('module')) {          
            $module = $mainPageModule;
            $method = $mainPageMethod;
        }
        else {
            $module = (string)$this->params()->fromRoute('module');
            
            if (null === $this->params()->fromRoute('method')) {
                $method = null;
            }
            else {
                $method = (string)$this->params()->fromRoute('method');
            }
        }
        
        if ($this->userAuthentication()->hasIdentity()) {
            $resultArray['user'] = $this->userAuthentication()->getIdentity()->toArray();
            unset($resultArray['user']['password']);
        } else {
            $resultArray['user'] = null;            
        }
        
        $resultArray['page'] = array(
            'link' => $this->request->getRequestUri(),
        );        
        $resultArray['root_url'] = ROOT_URL;
        $resultArray['module'] = $module;
        if (null === $method) {
            $resultArray['method'] = 'undefined';
        }
        else {
            $resultArray['method'] = $method;
        }
        
        $resultArray['parents'] = array();
                
        if ($this->moduleManager->isModuleActive($module)) {
            $moduleConfig = $this->moduleManager->getModuleConfig($module);
            
            $moduleConfig['title'] = $this->translator->translateI18n($moduleConfig['title']);
                
            if ($module == $mainPageModule && $method === $mainPageMethod) {
                $isMainPage = true;
            }
            else {
                $isMainPage = false;
            }

            if (!$isMainPage) {         
                $mainPageModuleConfig = $this->moduleManager->getModuleConfig($mainPageModule);

                $resultArray['parents'][] = array(
                    'link' => $this->url()->fromRoute('admin'),
                    'title' => $this->translator->translateI18n($mainPageModuleConfig['methods'][$mainPageMethod]['title']),
                );
            }

            if (null === $method) {
                $resultArray['page']['title'] = 'Не передан метод';
                $resultArray['page']['content'] = 'Не передан метод';
            } else {
                if (isset($moduleConfig['methods'][$method])) {
                    $moduleConfig['methods'][$method]['title'] = $this->translator->translateI18n($moduleConfig['methods'][$method]['title']);
                    
                    if (isset($moduleConfig['methods'][$method]['type']) && 'be' == $moduleConfig['methods'][$method]['type']) {
                        $resultArray['page'] = array_merge($resultArray['page'], $moduleConfig['methods'][$method]);
/*
                        if (isset($moduleConfig['methods'][$method]['availableReturnTypes'])) {
                            if (!in_array($format, $moduleConfig['methods'][$method]['availableReturnTypes'])) {
                                $format = $moduleConfig['methods'][$method]['defaultReturnType'];
                            }
                        }
*/

                        if (!$isMainPage) {
                            $tmpMethod = $moduleConfig['methods'][$method];
                            $tmpParents = array();
                            while (isset($tmpMethod['breadcrumbPrevMethod'])) {
                                if ($mainPageModule == $module && $mainPageMethod == $tmpMethod['breadcrumbPrevMethod']) {
                                    break;
                                }

                                $urlParams = array(
                                    'module' => $module,
                                    'method' => $tmpMethod['breadcrumbPrevMethod'],
                                );
                                   
                                array_unshift($tmpParents, array(
                                    'link' => $this->url()->fromRoute('admin/method', $urlParams),
                                    'title' => $this->translator->translateI18n($moduleConfig['methods'][$tmpMethod['breadcrumbPrevMethod']]['title']),
                                ));

                                $tmpMethod = $moduleConfig['methods'][$tmpMethod['breadcrumbPrevMethod']];
                            }
                            $resultArray['parents'] = array_merge($resultArray['parents'], $tmpParents);
                        }
                        
                        if ($this->isAllowed('be_method_access', $module . ':' . $method)) {
                            $serviceName = $moduleConfig['methods'][$method]['service'];

                            $instance = $this->serviceLocator->get('methodManager')->get($serviceName);
                            
                            $instance->init();
                            
                            if (is_callable(array($instance, 'main'))) {
                                $tmpResult = $instance->main();

                                if (is_array($tmpResult)) {
                                    $resultArray['page'] = array_merge($resultArray['page'], $tmpResult);
                                    if (isset($resultArray['page']['breadcrumbPrevLink'])) {
                                        $resultArray['parents'][] = array(
                                            'link' => $resultArray['page']['breadcrumbPrevLink']['link'],
                                            'title' => $resultArray['page']['breadcrumbPrevLink']['title'],
                                        );     
                                    }
                                }
                                else {
                                    $resultArray['page']['content'] = $tmpResult;
                                }
                            } else {
                                throw new \Exception('There is no method main() in class ' . get_class($instance));
                            }                        
                        }
                        else {
                            $resultArray['page']['content'] = 'У Вас нет привилегий на доступ к методу "' . $moduleConfig['methods'][$method]['title'] . '" (' . $module . ':' . $method . ')';
                        }
                    } else {
                        $resultArray['page']['title'] = 'Метод ' . $method . ' не является Backend методом';
                        $resultArray['page']['content'] = 'Метод ' . $method . ' не является Backend методом';
                    }                    
                }
                else {
                    $resultArray['page']['title'] = 'Метод ' . $method . ' не найден в модуле ' . $moduleConfig['title'];
                    $resultArray['page']['content'] = 'Метод ' . $method . ' не найден в модуле ' . $moduleConfig['title'];
                }
            }            
        }
        else {
            $resultArray['page']['title'] = 'Расширение ' . $module . ' не установлено';
            $resultArray['page']['content'] = 'Расширение ' . $module . ' не установлено';
        }
        
        
        $queryParams = $this->params()->fromQuery();
        ksort($queryParams);
        $resultArray['page']['canonicalUrl'] = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . $this->url()->fromRoute(null, array(), array(
            'query' => $queryParams,
        ), true);        
               
        
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
          
        if (isset($resultArray['page']['content']) && ($resultArray['page']['content'] instanceof Response)) {
            return $resultArray['page']['content'];
        }
        if ($this->response->isRedirect()) {
            return $this->response;
        }    
        
        $application = $this->serviceLocator->get('application');
        $eventManager = $application->getEventManager();
        
        $eventManager->trigger('prepare_output', $this, array($resultArray));
     
        switch ($format) {
            case 'html':                
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                
                $themePageTemplate = $this->config[CURRENT_THEME]['defaultTemplate'];
                
                $view = new ViewModel();     
                
                $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                
                if (isset($resultArray['page']['contentTemplate'])) {                    
                    $contentViewModel = new ViewModel();
                    $contentViewModel->setTemplate($resultArray['page']['contentTemplate']['name']);                    
                    if (isset($resultArray['page']['contentTemplate']['data'])) {
                        $contentViewModel->setVariables($resultArray['page']['contentTemplate']['data']);
                    }                    
                    $resultArray['page']['content'] = $viewRender->render($contentViewModel);
                    unset($resultArray['page']['contentTemplate']);
                }
                
                $view->setVariables($resultArray);                
                $view->setTemplate($themePageTemplate);
                                
                $wrapperViewModel = $this->layout();
                
                $wrapperViewModel->content = $viewRender->render($view);
                
                $eventManager->trigger('prepare_public_resources', $this, array($resultArray));
                
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

                $eventManager->trigger('prepare_public_resources', $this, array($resultArray));
                
                $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceLocator->get('viewHelperManager')));
                
                $result = json_encode($resultArray);

                break;
                
            case 'json_html': 
                
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'application/json; charset=utf-8'));

                if (!empty($resultArray['errors'])) {
                    foreach ($resultArray['errors'] as $k => $v) {
                        unset($resultArray['errors'][$k]['debug_backtrace']);
                        unset($resultArray['errors'][$k]['err_context']);
                    }
                }
                
                $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                
                if (isset($resultArray['page']['contentTemplate'])) {                    
                    $contentViewModel = new ViewModel();
                    $contentViewModel->setTemplate($resultArray['page']['contentTemplate']['name']);
                    if (isset($resultArray['page']['contentTemplate']['data'])) {
                        $contentViewModel->setVariables($resultArray['page']['contentTemplate']['data']);
                    } 
                    
                    $resultArray['page']['content'] = $viewRender->render($contentViewModel);
                    unset($resultArray['page']['contentTemplate']);
                }
                
                $eventManager->trigger('prepare_public_resources', $this, array($resultArray));
                
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
                
                $eventManager->trigger('prepare_public_resources', $this, array($resultArray));
                
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
                
                $eventManager->trigger('prepare_public_resources', $this, array($resultArray));
                
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
                
                $eventManager->trigger('prepare_public_resources', $this, array($resultArray));

                $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceLocator->get('viewHelperManager')));

                $result = '<pre>' . print_r($resultArray, true) . '</pre>';
                
                break;

            default:
                throw new Zend_Exception('unknown return type ' . $returnType);                
        }

        $args = array(
            'resultArray' => $resultArray,
            'result' => $result,
            'format' => $format,
        );
        
        $args = $eventManager->prepareArgs($args);
        
        $eventManager->trigger('prepare_output.post', $this, $args);
        
        $this->response->setContent($args['result']); 
        return $this->response;        
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
