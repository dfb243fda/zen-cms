<?php

namespace FrontEnd\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class UserData implements ServiceManagerAwareInterface
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
        
    public function getUserData()
    {        
        $userAuth = $this->serviceManager->get('ControllerPluginManager')->get('userAuthentication');
        
        if ($userAuth->hasIdentity()) {
            $userData = $userAuth->getIdentity()->toArray();
            unset($userData['password']);
        } else {
            $userData = null;            
        }
        
        return $userData;
    }
}