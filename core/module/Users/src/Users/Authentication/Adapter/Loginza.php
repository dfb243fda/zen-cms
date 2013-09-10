<?php

namespace Users\Authentication\Adapter;

use Zend\Authentication\Result as AuthenticationResult;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Users\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use Users\Mapper\User as UserMapperInterface;
use Users\Options\AuthenticationOptionsInterface;

class Loginza extends AbstractAdapter implements ServiceManagerAwareInterface
{
    /**
     * @var UserMapperInterface
     */
    protected $usersCollection;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    protected $options;

    public function authenticate(AuthEvent $e)
    {        
        if ($this->isSatisfied()) {
            $storage = $this->getStorage()->read();
            $e->setIdentity($storage['identity'])
              ->setCode(AuthenticationResult::SUCCESS)
              ->setMessages(array('Authentication successful.'));
            return;
        }
        
        $data = $e->getData();
        
        $identity   = $data['identity'];
        $userEntity = NULL;
        
        $options = $this->getOptions();

        if ($identity) {
            $userEntity = $this->getUsersCollection()->getUserByLoginzaId($identity);
        }        

        if (!$userEntity) {
            $e->setCode(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND)
              ->setMessages(array('A record with the supplied identity could not be found.'));
            $this->setSatisfied(false);
            return false;
        }

        if ($options['enableUserState']) {
            // Don't allow user to login if state is not in allowed list
            if (!in_array($userEntity->getState(), $this->getOptions()->getAllowedLoginStates())) {
                $e->setCode(AuthenticationResult::FAILURE_UNCATEGORIZED)
                  ->setMessages(array('A record with the supplied identity is not active.'));
                $this->setSatisfied(false);
                return false;
            }
        }

        // Success!
        $e->setIdentity($userEntity->getId());
        $this->setSatisfied(true);
        $storage = $this->getStorage()->read();
        $storage['identity'] = $e->getIdentity();
        $this->getStorage()->write($storage);
        $e->setCode(AuthenticationResult::SUCCESS)
          ->setMessages(array('Authentication successful.'));
    }

    /**
     * @return \Users\Collection\Users
     */
    public function getUsersCollection()
    {
        if (null === $this->usersCollection) {
            $this->usersCollection = $this->getServiceManager()->get('Users\Collection\Users');
        }
        return $this->usersCollection;
    }

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
     * @param ServiceManager $locator
     * @return void
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param AuthenticationOptionsInterface $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return AuthenticationOptionsInterface
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $config = $this->getServiceManager()->get('config');
            
            $options = $config['Users'];
            
            $this->setOptions($options);
        }
        return $this->options;
    }
}
