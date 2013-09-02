<?php

namespace Installer\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class DemoSites implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getDemoSites()
    {        
        $translator = $this->serviceManager->get('translator');
        $config = $this->serviceManager->get('config');
        
        if (isset($config['Installer']['demoSites'])) {
            $demoSites = $config['Installer']['demoSites'];
            
            foreach ($demoSites as $k=>$v) {
                $demoSites[$k]['title'] = $translator->translateI18n($v['title']);
            }
        } else {
            $demoSites = array();
        }
        
        return $demoSites;
    }
    
}