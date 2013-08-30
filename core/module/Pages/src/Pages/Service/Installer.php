<?php

namespace Pages\Service;

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
        $pageTypesDetector = $this->serviceManager->get('Pages\Service\PageTypesDetector');
        $pageTypesDetector->detect();
        
        $contentTypesDetector = $this->serviceManager->get('Pages\Service\ContentTypesDetector');
        $contentTypesDetector->detect();
        
        $configManager = $this->serviceManager->get('configManager');
        
        $configManager->set('pages', 'replace_spaces_with', '_');
    }
}