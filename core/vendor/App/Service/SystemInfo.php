<?php

namespace App\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class SystemInfo implements ServiceManagerAwareInterface
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
    
    public function getSystemInfo()
    {
        $profiler = $this->serviceManager->get('db')->getProfiler();
        $sqlQueriesCnt = count($profiler->getProfiles());
    
        $result = array(
            'execTime' => round(microtime(true) - TIME_START, 3),
            'maxMemory' => (int)(memory_get_peak_usage() / 1024),
            'includedFilesCnt' => count(get_included_files()),
            'sqlQueriesCnt' => $sqlQueriesCnt,
        );
        
        return $result;
    }
}