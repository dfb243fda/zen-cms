<?php

namespace ThemeSwitch\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ThemeSwitch implements ServiceManagerAwareInterface
{
    protected $serviceManager;    
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setBeTheme($theme)
    {
        $this->serviceManager->get('configManager')->set('system', 'default_be_theme', $theme);
    }
    
    public function setFeTheme($theme)
    {
        $this->serviceManager->get('configManager')->set('system', 'fe_theme', $theme);
    }
    
    public function getThemes()
    {
        $modules = $this->serviceManager->get('ModuleManager')->getActiveModules();
        $configManager = $this->serviceManager->get('ConfigManager');
        
        $beThemes = array();
        $feThemes = array();
        
        foreach ($modules as $moduleKey => $moduleConfig) {
            if (isset($moduleConfig['type'])) {
                if (isset($moduleConfig['themeImage'])) {
                    $moduleConfig['themeImagePath'] = PUBLIC_PATH . $moduleConfig['themeImage'];
                }
                
                if ('be_theme' == $moduleConfig['type']) {
                    $moduleConfig['isCurrentTheme'] = false;
                    if ($moduleKey == $configManager->get('system', 'default_be_theme')) {
                        $moduleConfig['isCurrentTheme'] = true;
                    }
                    $beThemes[$moduleKey] = $moduleConfig;
                } elseif ('fe_theme' == $moduleConfig['type']) {
                    $moduleConfig['isCurrentTheme'] = false;
                    if ($moduleKey == $configManager->get('system', 'fe_theme')) {
                        $moduleConfig['isCurrentTheme'] = true;
                    }
                    $feThemes[$moduleKey] = $moduleConfig;
                }
            }
        }
        
        $themes = array(
            'be' => array(
                'title' => 'i18n::ThemeSwitch:Be themes',
                'items' => $beThemes,
            ),
            'fe' => array(
                'title' => 'i18n::ThemeSwitch:Fe themes',
                'items' => $feThemes,
            ),
        );
        
        return $themes;
    }
}