<?php

namespace FrontEnd\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Pages\Service\PageType;
use Zend\Stdlib\ResponseInterface as Response;

class FeController extends AbstractActionController
{
    protected $bugHunter;
    
    protected $applicationConfig;
    
    protected $translator;
    
    protected $configManager;
    
    protected $moduleManager;
    
    protected $config;
        
    protected $viewManager;
    
    public function init()
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
        
        define('CURRENT_THEME', $this->configManager->get('system', 'fe_theme'));
        
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
        
        $userIdentity = $this->userAuthentication()->getIdentity();
        if (null === $userIdentity) {
            $resultArray['user'] = null;
        } else {
            $resultArray['user'] = $userIdentity->toArray();
            unset($resultArray['user']['password']);
        }  
        
        $resultArray['root_url'] = ROOT_URL;
        
        $page = $this->serviceLocator->get('Pages\Service\Page');
        
        $resultArray['page'] = $page->getPageData();
        
        if (isset($resultArray['page']['page_type_id'])) {
            $pageType = new PageType($this->serviceLocator, $resultArray['page']['page_type_id']);
        
            $pageType->prepareResult($resultArray);
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
        
        if (isset($resultArray['page']['content']) && ($resultArray['page']['content'] instanceof Response)) {
            return $resultArray['page']['content'];
        }
        if (isset($resultArray['page']['redirectUrl'])) {
            return $this->redirect()->toUrl($resultArray['page']['redirectUrl']);
        }
        if ($this->response->isRedirect()) {
            return $this->response;
        }       
        
        $this->response->setStatusCode($resultArray['page']['statusCode']);
   
        if (isset($resultArray['page']['language'])) {
            $this->translator->setLocale($resultArray['page']['language']);
        }
        
        $application = $this->serviceLocator->get('application');
        $eventManager = $application->getEventManager();
        
        $eventManager->trigger('prepare_output', $this, array($resultArray));
        
        switch ($format) {
            case 'html':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                
                $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                
                if (!empty($resultArray['page']['content'])) {
                    foreach ($resultArray['page']['content'] as $marker=>$content) {
                        $str = '';
                        foreach ($content as $v) {
                            $tmpTemplateData = $page->getTemplate($v['template']);
                         
                            $tmpView = new ViewModel();
                            $tmpView->setTemplate($tmpTemplateData['type'] . '/' . $tmpTemplateData['module'] . '/' . $tmpTemplateData['method'] . '/' . $tmpTemplateData['name']);
                            
                            $tmpView->setVariables($v);
                            
                            $str .= $viewRender->render($tmpView);                            
                        }
                        
                        $resultArray['page']['content'][$marker] = $str;
                    }
                }
                
                $view = new ViewModel();  
                $view->setVariables($resultArray);  
                
                if (isset($resultArray['page']['template'])) {                    
                    $tmpTemplateData = $page->getTemplate($resultArray['page']['template']);
                    $view->setTemplate($tmpTemplateData['type'] . '/' . $tmpTemplateData['module'] . '/' . $tmpTemplateData['name']);
                } else {
                    return $resultArray;
                }
                
                
                                 
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
                
                if (!empty($resultArray['page']['content'])) {
                    foreach ($resultArray['page']['content'] as $marker=>$content) {
                        $str = '';
                        foreach ($content as $v) {
                            $tmpTemplateData = $page->getTemplate($v['template']);
                         
                            $tmpView = new ViewModel();
                            $tmpView->setTemplate($tmpTemplateData['type'] . '/' . $tmpTemplateData['module'] . '/' . $tmpTemplateData['method'] . '/' . $tmpTemplateData['name']);
                            
                            $tmpView->setVariables($v);
                            
                            $str .= $viewRender->render($tmpView);                            
                        }
                        
                        $resultArray['page']['content'][$marker] = $str;
                    }
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
                throw new \Exception('unknown return type ' . $returnType);                
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
