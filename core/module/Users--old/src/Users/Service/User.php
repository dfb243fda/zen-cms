<?php

namespace Users\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Crypt\Password\Bcrypt;
use Zend\Form\Form;
use Users\Options\UserServiceOptionsInterface;
use Zend\Stdlib\Hydrator;
use ZfcBase\EventManager\EventProvider;
use ZfcUser\Mapper\UserInterface as UserMapperInterface;

class User extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * @var AuthenticationService
     */
    protected $authService;
    
    protected $options;
        
    protected $formHydrator;
    
    protected $userMapper;
    
    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
    
    public function getAuthService()
    {
        if (null === $this->authService) {
            $this->authService = $this->getServiceManager()->get('users_auth_service');
        }
        return $this->authService;
    }
    
        
    /**
     * Return the Form Hydrator
     *
     * @return \Zend\Stdlib\Hydrator\ClassMethods
     */
    public function getFormHydrator()
    {
        if (!$this->formHydrator instanceof Hydrator\ClassMethods) {
            $this->setFormHydrator($this->getServiceManager()->get('users_register_form_hydrator'));
        }

        return $this->formHydrator;
    }

    /**
     * Set the Form Hydrator to use
     *
     * @param Hydrator\ClassMethods $formHydrator
     * @return User
     */
    public function setFormHydrator(Hydrator\ClassMethods $formHydrator)
    {
        $this->formHydrator = $formHydrator;
        return $this;
    }
    
    /**
     * get service options
     *
     * @return UserServiceOptionsInterface
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $config = $this->getServiceManager()->get('config');
            
            $this->setOptions($config['Users']);
        }
        return $this->options;
    }

    /**
     * set service options
     *
     * @param UserServiceOptionsInterface $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
    
    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceManager()->get('users_mapper');
        }
        return $this->userMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setUserMapper(UserMapperInterface $userMapper)
    {
        $this->userMapper = $userMapper;
        return $this;
    }
}

