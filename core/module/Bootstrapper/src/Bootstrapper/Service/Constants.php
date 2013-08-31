<?php

namespace Bootstrapper\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Constants implements ServiceManagerAwareInterface
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
    
    public function defineConstants()
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        $request = $this->serviceManager->get('request');
        $uri = $request->getUri();
        
        define('ROOT_URL_SEGMENT', $request->getBasePath());
        define('ROOT_URL', $uri->getScheme() . '://' . $uri->getHost() . ROOT_URL_SEGMENT);
        define('REQUEST_URL_SEGMENT', $request->getRequestUri());
        define('REQUEST_URL', $uri->getScheme() . '://' . $uri->getHost() . $request->getRequestUri());

        define('DB_PREF', $appConfig['dbPref']);    
    }
}