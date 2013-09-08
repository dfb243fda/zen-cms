<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Form;
use Users\Options\UserControllerOptionsInterface;


class LogoutController extends AbstractActionController
{
    /**
     * @var UserControllerOptionsInterface
     */
    protected $options;
    
    public function indexAction()
    {
        $this->userAuthentication()->getAuthAdapter()->resetAdapters();
        $this->userAuthentication()->getAuthAdapter()->logoutAdapters();
        $this->userAuthentication()->getAuthService()->clearIdentity();

        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        $options = $this->getOptions();
        
        if ($options['useRedirectParameterIfPresent'] && $redirect) {
            return $this->redirect()->toUrl($redirect);
        }

        return $this->redirect()->toRoute($options['logoutRedirectRoute']);
    }
    
    /**
     * set options
     *
     * @param UserControllerOptionsInterface $options
     * @return UserController
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * get options
     *
     * @return UserControllerOptionsInterface
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $config = $this->getServiceLocator()->get('config');
            $options = $config['Users'];
            $this->setOptions($options);
        }
        return $this->options;
    }
}