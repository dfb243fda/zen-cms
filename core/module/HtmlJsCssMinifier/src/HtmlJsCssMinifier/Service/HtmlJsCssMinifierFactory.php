<?php

namespace HtmlJsCssMinifier\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HtmlJsCssMinifierFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        
        $configManager = $serviceLocator->get('configManager');
                
        $instance = new HtmlJsCssMinifier();
        
        $instance->setMinifyCss($configManager->get('HtmlJsCssMinifier', 'minifyCss'))
                 ->setMinifyJs($configManager->get('HtmlJsCssMinifier', 'minifyJs'));
        
        if (isset($config['js_css_minifier']['ignoreJsFiles'])) {
            $instance->setIgnoreJsFiles($config['js_css_minifier']['ignoreJsFiles']);
        }
        if (isset($config['js_css_minifier']['ignoreCssFiles'])) {
            $instance->setIgnoreCssFiles($config['js_css_minifier']['ignoreCssFiles']);
        }
        
        
        return $instance;
    }
}