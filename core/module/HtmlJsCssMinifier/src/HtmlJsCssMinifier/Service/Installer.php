<?php

namespace HtmlJsCssMinifier\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Installs Pages module
 */
class Installer implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function install()
    {
        $configManager = $this->serviceManager->get('configManager');
        
        if (!$configManager->has('HtmlJsCssMinifier', 'minifyHtml')) {
            $configManager->set('HtmlJsCssMinifier', 'minifyHtml', true);
        }
        if (!$configManager->has('HtmlJsCssMinifier', 'minifyJs')) {
            $configManager->set('HtmlJsCssMinifier', 'minifyJs', true);
        }
        if (!$configManager->has('HtmlJsCssMinifier', 'minifyCss')) {
            $configManager->set('HtmlJsCssMinifier', 'minifyCss', true);
        }        
    }
}