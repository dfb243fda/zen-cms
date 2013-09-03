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
        
        $db = $this->serviceManager->get('db');
        $request = $this->serviceManager->get('request');
        $uri = $request->getUri();     
        $host = $uri->getHost() . $request->getBasePath();
        $db->query('
            insert into ' . DB_PREF . 'domains
                (host, is_default, default_lang_id)
            values
                (?, ?, ?)
        ', array($host, 1, 1));
    }
}