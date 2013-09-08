<?php

namespace Users\Service;

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
        $authService = $this->serviceManager->get('users_auth_service');
        
        $currentUser = $authService->getUser();          
               
        if ($currentUser) {
            $userData = $currentUser->toArray();
            unset($userData['password']);
        } else {
            $userData = null;
        }
        
        return $userData;
    }
}