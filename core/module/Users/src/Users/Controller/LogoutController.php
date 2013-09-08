<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LogoutController extends AbstractActionController
{    
    public function indexAction()
    {
        $authService = $this->serviceLocator->get('users_auth_service');
        $authService->setAdapter($this->serviceLocator->get('Users\Authentication\Adapter\AdapterChain'));
        
        $config = $this->getServiceLocator()->get('config');
        
        $authService->getAdapter()->resetAdapters();
        $authService->getAdapter()->logoutAdapters();
        $authService->clearIdentity();

        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        if ($config['Users']['useRedirectParameterIfPresent'] && $redirect) {
            return $this->redirect()->toUrl($redirect);
        }

        return $this->redirect()->toRoute($config['Users']['logoutRedirectRoute']);
    }
}