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
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
        
    public function authenticate($request)
    {                
        $formElementManager = $this->serviceManager->get('FormElementManager');
        
        $form = $formElementManager->get('Users\Form\LoginForm');
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return false;
        }

        $authService = $this->serviceManager->get('users_auth_service');
        $authService->setAdapter($this->serviceManager->get('Users\Authentication\Adapter\AdapterChain'));

       // clear adapters
        $authService->getAdapter()->resetAdapters();
        $authService->clearIdentity();

        $result = $authService->getAdapter()->prepareForAuthentication($request);

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
}