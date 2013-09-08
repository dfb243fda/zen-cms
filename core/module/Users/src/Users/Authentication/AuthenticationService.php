<?php

namespace Users\Authentication;

use Zend\Authentication\AuthenticationService as ZendAuthenticationService;

class AuthenticationService extends ZendAuthenticationService
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    public function __construct(
        \Zend\ServiceManager\ServiceManager $sm, 
        \Zend\Authentication\Storage\StorageInterface $storage = null,
        \Zend\Authentication\Adapter\AdapterInterface $adapter = null)
    {
        parent::__construct($storage, $adapter);
        
        $this->serviceManager = $sm;
    }


    public function getUser()
    {
        if ($this->hasIdentity()) {
            $identity = $this->getIdentity();

            $usersCollection = $this->serviceManager->get('Users\Collection\Users');
            return $usersCollection->getUserById($identity);
        }
        return null;
    }
}