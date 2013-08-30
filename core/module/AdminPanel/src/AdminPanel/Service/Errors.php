<?php

namespace AdminPanel\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Errors implements ServiceManagerAwareInterface
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
    
    public function getErrors()
    {
        $bugHunter = $this->serviceManager->get('bugHunter');  
        $applicationConfig = $this->serviceManager->get('ApplicationConfig');    
        $translator = $this->serviceManager->get('translator');   
        
        $result = array();
        
        if ($bugHunter->hasLogs()) {            
            if ($this->isAllowed('get_errors') || true == $applicationConfig['show_errors_to_everybody']) {
                $result['access'] = true;
            } else {
                $result['access'] = false;
                $result['msg'] = $translator->translate('There are some errors on this page, sorry for temporary inconvenience');
            }          
            $tmp = $bugHunter->getLogs();
            foreach ($tmp as $k=>$v) {
                unset($tmp[$k]['extra']['context']);
            }
            $result['list'] = $tmp;  
        }
        
        return $result;
    }
}