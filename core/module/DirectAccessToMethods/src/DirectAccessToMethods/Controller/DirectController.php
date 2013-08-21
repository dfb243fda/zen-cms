<?php

namespace DirectAccessToMethods\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class DirectController extends AbstractActionController
{        
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
        
        if (null === $this->params()->fromRoute('module')) {
            throw new \Exception($this->translator->translate('Wrong parameters transferred'));
        }
        if (null === $this->params()->fromRoute('method')) {
            throw new \Exception($this->translator->translate('Wrong parameters transferred'));
        }
        
        if ($this->request->isXmlHttpRequest()) {
            $defaultFormat = 'json';
        }
        
        if (null === $this->params()->fromRoute('format')) {
            $format = $defaultFormat;
        }
        else {
            $format = (string)$this->params()->fromRoute('format');
            if (!in_array($format, array('json', 'xml', 'var_dump', 'print_r'))) {
                $format = $defaultFormat;
            }
        } 
        
        
        $module = (string)$this->params()->fromRoute('module');
        $method = (string)$this->params()->fromRoute('method');
        
        
        $resultArray = array();
        
        if ($this->moduleManager->isModuleActive($module)) {
            if ($this->moduleManager->isMethodExists($module, $method)) {
                $moduleConfig = $this->moduleManager->getModuleConfig($module);

                if (isset($moduleConfig['methods'][$method]['directAccess']) && $moduleConfig['methods'][$method]['directAccess']) {
                    $serviceName = $moduleConfig['methods'][$method]['service'];

                    $instance = $this->serviceLocator->get('methodManager')->get($serviceName);

                    $instance->init();

                    if (is_callable(array($instance, 'main'))) {
                        $args = array();
                        for ($i = 1; $i <= 5; $i++) {
                            if (null === $this->params()->fromRoute('param' . $i)) {
                                break;
                            }
                            $args[] = $this->params()->fromRoute('param' . $i);
                        }    

                        $resultArray['result'] = call_user_func_array(array($instance, 'main'), $args);
                    } else {
                        throw new \Exception('There is no method main() in class ' . get_class($instance));
                    }  
                } else {
                    throw new \Exception($this->translator->translate('This method does not support direct access'));
                }
            } else {
                 throw new \Exception('Method ' . $method . ' is not exists in module ' . $module);                
            }
        } else {
            throw new \Exception('Module ' . $module . ' is not active');
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
        
        if (!empty($resultArray['errors'])) {
            foreach ($resultArray['errors'] as $k => $v) {
                unset($resultArray['errors'][$k]['debug_backtrace']);
                unset($resultArray['errors'][$k]['err_context']);
            }
        }

        $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceLocator->get('viewHelperManager')));
        
        switch ($format) {
            case 'json':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'application/json; charset=utf-8'));

                $result = json_encode($resultArray);

                break;

            case 'xml':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/xml; charset=utf-8'));
                
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
                                
                $result = '<pre>' . var_export($resultArray, true) . '</pre>';
                
                break;
                
            case 'print_r':
                $this->response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                
                $result = '<pre>' . print_r($resultArray, true) . '</pre>';
                
                break;

            default:
                throw new Zend_Exception('unknown return type ' . $returnType);                
        }

        $this->response->setContent($result); 
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