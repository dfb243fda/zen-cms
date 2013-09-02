<?php

namespace AdminPanel\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class PageData implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $module;
    
    protected $method;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function detectModuleAndMethod()
    {
        $params = $this->serviceManager->get('ControllerPluginManager')->get('params');
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $currentThemeConfig = $moduleManager->getModuleConfig(CURRENT_THEME);
        
        $mainPageModule = $currentThemeConfig['be_main_page_method'][0];   
        $mainPageMethod = $currentThemeConfig['be_main_page_method'][1];
        
        if (null === $params->fromRoute('module')) {          
            $module = $mainPageModule;
            $method = $mainPageMethod;
        }
        else {
            $module = (string)$params->fromRoute('module');
            
            if (null === $params->fromRoute('method')) {
                $method = null;
            }
            else {
                $method = (string)$params->fromRoute('method');
            }
        }
        
        $this->module = $module;
        $this->method = $method;
    }
    
    public function getModule()
    {        
        return $this->module;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function getPageData()
    {
        $configManager = $this->serviceManager->get('configManager');
        $request = $this->serviceManager->get('request');
        $moduleManager = $this->serviceManager->get('moduleManager');
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        $methodManager = $this->serviceManager->get('methodManager');
        $translator = $this->serviceManager->get('translator');
        $isAllowed = $this->serviceManager->get('ControllerPluginManager')->get('isAllowed');
        $paramsPlugin = $this->serviceManager->get('ControllerPluginManager')->get('params');
                
        $currentThemeConfig = $moduleManager->getModuleConfig(CURRENT_THEME);
        
        $mainPageModule = $currentThemeConfig['be_main_page_method'][0];   
        $mainPageMethod = $currentThemeConfig['be_main_page_method'][1];
        
        $module = $this->module;
        $method = $this->method;
        
        $result = array();
        
        $result['link'] = $request->getRequestUri();
        
        if ($moduleManager->isModuleActive($module)) {
            $moduleConfig = $moduleManager->getModuleConfig($module);
            
            $moduleConfig['title'] = $translator->translateI18n($moduleConfig['title']);
                
            if ($module == $mainPageModule && $method === $mainPageMethod) {
                $isMainPage = true;
            }
            else {
                $isMainPage = false;
            }
            
            if (null === $method) {
                $result['title'] = 'Не передан метод';
                $result['content'] = 'Не передан метод';
            } else {
                if (isset($moduleConfig['methods'][$method])) {
                    $moduleConfig['methods'][$method]['title'] = $translator->translateI18n($moduleConfig['methods'][$method]['title']);
                    
                    if (isset($moduleConfig['methods'][$method]['type']) && 'be' == $moduleConfig['methods'][$method]['type']) {
                        $result = array_merge($result, $moduleConfig['methods'][$method]);
                        
                        if ($isAllowed('be_method_access', $module . ':' . $method)) {
                            $serviceName = $moduleConfig['methods'][$method]['service'];

                            $instance = $methodManager->get($serviceName);
                            
                            $instance->init();
                            
                            if (is_callable(array($instance, 'main'))) {
                                $tmpResult = $instance->main();

                                if (is_array($tmpResult)) {
                                    $result = array_merge($result, $tmpResult);
                                }
                                else {
                                    $result['content'] = $tmpResult;
                                }
                            } else {
                                throw new \Exception('There is no method main() in class ' . get_class($instance));
                            }                        
                        }
                        else {
                            $result['content'] = 'У Вас нет привилегий на доступ к методу "' . $moduleConfig['methods'][$method]['title'] . '" (' . $module . ':' . $method . ')';
                        }
                    } else {
                        $result['title'] = 'Метод ' . $method . ' не является Backend методом';
                        $result['content'] = 'Метод ' . $method . ' не является Backend методом';
                    }                    
                }
                else {
                    $result['title'] = 'Метод ' . $method . ' не найден в модуле ' . $moduleConfig['title'];
                    $result['content'] = 'Метод ' . $method . ' не найден в модуле ' . $moduleConfig['title'];
                }
            }
        }
        
        $queryParams = $paramsPlugin->fromQuery();
        ksort($queryParams);
        $result['canonicalUrl'] = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $urlPlugin->fromRoute(null, array(), array(
            'query' => $queryParams,
        ), true);    
        
        return $result;
    }
    
    public function getPageParentsData($pageData)
    {
        $configManager = $this->serviceManager->get('configManager');
        $moduleManager = $this->serviceManager->get('moduleManager');
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        $translator = $this->serviceManager->get('translator');
                
        $currentThemeConfig = $moduleManager->getModuleConfig(CURRENT_THEME);
        
        $mainPageModule = $currentThemeConfig['be_main_page_method'][0];   
        $mainPageMethod = $currentThemeConfig['be_main_page_method'][1];
        
        $module = $this->module;
        $method = $this->method;
        
        $result = array();
                
        if ($moduleManager->isModuleActive($module)) {
            $moduleConfig = $moduleManager->getModuleConfig($module);
            
            $moduleConfig['title'] = $translator->translateI18n($moduleConfig['title']);
                
            if ($module == $mainPageModule && $method === $mainPageMethod) {
                $isMainPage = true;
            }
            else {
                $isMainPage = false;
            }

            if (!$isMainPage) {         
                $mainPageModuleConfig = $moduleManager->getModuleConfig($mainPageModule);

                $result[] = array(
                    'link' => $urlPlugin->fromRoute('admin'),
                    'title' => $translator->translateI18n($mainPageModuleConfig['methods'][$mainPageMethod]['title']),
                );
            }

            if (null !== $method) {
                if (isset($moduleConfig['methods'][$method])) {
                    $moduleConfig['methods'][$method]['title'] = $translator->translateI18n($moduleConfig['methods'][$method]['title']);
                    
                    if (isset($moduleConfig['methods'][$method]['type']) && 'be' == $moduleConfig['methods'][$method]['type']) {

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
                                    'link' => $urlPlugin->fromRoute('admin/method', $urlParams),
                                    'title' => $translator->translateI18n($moduleConfig['methods'][$tmpMethod['breadcrumbPrevMethod']]['title']),
                                ));

                                $tmpMethod = $moduleConfig['methods'][$tmpMethod['breadcrumbPrevMethod']];
                            }
                            $result = array_merge($result, $tmpParents);
                        }
                    }                 
                }
            }            
        }
        
        if (isset($pageData['breadcrumbPrevLink'])) {
            $result[] = array(
                'link' => $pageData['breadcrumbPrevLink']['link'],
                'title' => $pageData['breadcrumbPrevLink']['title'],
            );     
        }
        
        return $result;
    }
    
    
}