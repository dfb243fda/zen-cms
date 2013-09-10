<?php

namespace Users\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Authentication\AuthenticationService;
use Users\Entity\UserInterface as User;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class UserDisplayName extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var AuthenticationService
     */
    protected $authService;
    
    protected $serviceLocator;
    
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    

    /**
     * __invoke
     *
     * @access public
     * @param \Users\Entity\UserInterface $user
     * @return String
     */
    public function __invoke(User $user = null)
    {
        if (null === $user) {       
            $rootServiceManager = $this->serviceLocator->getServiceLocator();
            
            $authService = $rootServiceManager->get('users_auth_service');
            
            if ($authService->hasIdentity()) {
                $user = $authService->getUser();
            } else {
                return null;
            }
        }

        $displayName = $user->getDisplayName();
        if (null === $displayName) {
            $displayName = $user->getLogin();
        }
        if (null === $displayName) {
            $displayName = $user->getEmail();
            $displayName = substr($displayName, 0, strpos($displayName, '@'));
        }

        return $displayName;
    }
}
