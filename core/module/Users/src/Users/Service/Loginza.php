<?php

namespace Users\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Loginza implements ServiceManagerAwareInterface
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
    
    public function getLoginzaConfig()
    {
        $configManager = $this->serviceManager->get('configManager');
        $request = $this->serviceManager->get('request');
        
        $domainsConfig = $configManager->get('loginza', 'domains');
        
        $allDomainsConfig = null;
        $currentDomainConfig = null;
        
        $host = $request->getUri()->getHost() . $request->getBasePath();
        
        foreach ($domainsConfig as $value) {
            if ($value['allow_loginza']) {
                if (!$value['domain']) {
                    $allDomainsConfig = $value;
                } elseif ($value['domain'] == $host) {
                    $currentDomainConfig = $value;
                    break;
                }
            }
        }
        
        if ($currentDomainConfig) {
            return $currentDomainConfig;
        } elseif ($allDomainsConfig) {
            return $allDomainsConfig;
        }
        return false;
    }
}