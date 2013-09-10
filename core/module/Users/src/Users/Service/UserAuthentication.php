<?php

namespace Users\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Http\PhpEnvironment\Response;

class UserAuthentication implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $adaptersType = 'simple';
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setAdaptersType($type)
    {
        $this->adaptersType = $type;
        return $this;
    }
        
    public function authenticate($data = null)
    {    
        $request = $this->serviceManager->get('request');
        
        $authService = $this->serviceManager->get('users_auth_service');
        
        $this->serviceManager->get('Users\Service\AuthenticationAdapters')
                             ->setAdaptersType($this->adaptersType);
        
        $authService->setAdapter($this->serviceManager->get('Users\Authentication\Adapter\AdapterChain'));

       // clear adapters
        $authService->getAdapter()->resetAdapters();
        $authService->clearIdentity();

        $result = $authService->getAdapter()->prepareForAuthentication($request, $data);

        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return false;
        }

        $auth = $authService->authenticate();

        if (!$auth->isValid()) {
            return false;
        }
        return true;
    }
    
    public function getAdapter()
    {
        $authService = $this->serviceManager->get('users_auth_service');
        return $authService->getAdapter();
    }
}