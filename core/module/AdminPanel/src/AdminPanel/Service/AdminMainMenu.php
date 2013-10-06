<?php

namespace AdminPanel\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class AdminMainMenu implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    protected $defaultMenuGroupWeight = 10;
    
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getMainMenu()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $controllerPluginManager = $this->serviceManager->get('controllerPluginManager');        
        
        $isAllowed = $controllerPluginManager->get('isAllowed');
        $urlPlugin = $controllerPluginManager->get('url');
        $paramsPlugin = $controllerPluginManager->get('params');
        
        $blocks = $this->getMenuGroups();
        
        foreach ($blocks as $block_key => $block_data) {
            foreach ($block_data['items'] as $k => $v) {
                if (! $isAllowed('be_method_access', $v['module'] . ':' . $v['method']) ) {
                    unset($blocks[$block_key]['items'][$k]);
                }
            }
        }
        
        foreach ($blocks as $block_key => $block_data) {
            foreach ($block_data['items'] as $k => $v) {                
                $params = array(
                    'module' => $v['module'],
                    'method' => $v['method'],
                );
                if (isset($v['additional_params'])) {
                    $params = array_merge($params, $v['additional_params']);
                }
                
                $blocks[$block_key]['items'][$k]['link'] = $urlPlugin->fromRoute('admin/method', $params);
                
                if ($paramsPlugin->fromRoute('module') == $v['module'] && $paramsPlugin->fromRoute('method') == $v['method']) {
                    $blocks[$block_key]['items'][$k]['active'] = true;
                } else {
                    $blocks[$block_key]['items'][$k]['active'] = false;
                }
            }
        }
        
        return $blocks;
    }
    
    
    protected function getMenuGroups()
    {
        $config = $this->serviceManager->get('config');
        $translator = $this->serviceManager->get('translator');
        $application = $this->serviceManager->get('application');
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $modules = $moduleManager->getActiveModules();

        $menuGroups = $config['menu_groups'];
        
        foreach ($menuGroups as $k=>$v) {
            $menuGroups[$k]['title'] = $translator->translateI18n($menuGroups[$k]['title']);
            $menuGroups[$k]['items'] = array();
            if (!isset($v['weight'])) {
                $menuGroups[$k]['weight'] = $this->defaultMenuGroupWeight;
            }
        }

        uasort($menuGroups, function($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] < $b['weight']) ? -1 : 1;
        });
        
        foreach ($modules as $moduleKey => $module) {
            if (!empty($module['methods'])) {
                foreach ($module['methods'] as $methodKey => $method) {
                    if (isset($method['menu_group'])) {
                        if (isset($menuGroups[$method['menu_group']])) {
                            $method['module'] = $moduleKey;
                            $method['method'] = $methodKey;
                            $method['title'] = $translator->translateI18n($method['title']);
                            $menuGroups[$method['menu_group']]['items'][] = $method;
                        }
                        else {
                            throw new \Exception('Menu group not found ' . $method['menu_group'] . ' in module ' . $method['title'] . ' (' . $methodKey . ')');
                        }
                    }
                }        
            }            
        }
        
        $menuGroups = $application->getEventManager()->prepareArgs($menuGroups);
        $application->getEventManager()->trigger('get_menu_groups', $this, $menuGroups);
        $menuGroups = (array)$menuGroups;
        
        return $menuGroups;
    }
}