<?php

namespace Users\Authentication\Adapter;

use Zend\Authentication\Result as AuthenticationResult;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Crypt\Password\Bcrypt;
use Users\Authentication\Adapter\AdapterChainEvent as AuthEvent;
use Users\Mapper\User as UserMapperInterface;
use Users\Options\AuthenticationOptionsInterface;

class Db extends AbstractAdapter implements ServiceManagerAwareInterface
{
    /**
     * @var UserMapperInterface
     */
    protected $usersCollection;

    /**
     * @var closure / invokable object
     */
    protected $credentialPreprocessor;

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

        $identity   = $e->getRequest()->getPost()->get('identity');
        $credential = $e->getRequest()->getPost()->get('credential');
        $credential = $this->preProcessCredential($credential);
        $userEntity = NULL;
        
        $options = $this->getOptions();

        // Cycle through the configured identity sources and test each
        $fields = $options['authIdentityFields'];
        while ( !is_object($userEntity) && count($fields) > 0 ) {
            $mode = array_shift($fields);
            switch ($mode) {
                case 'username':
                    $userEntity = $this->getUsersCollection()->getUserByName($identity);
                    break;
                case 'email':
                    $userEntity = $this->getUsersCollection()->getUserByEmail($identity);
                    break;
            }
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

        $bcrypt = new Bcrypt();
        $bcrypt->setCost($options['passwordCost']);
        if (!$bcrypt->verify($credential,$userEntity->getPassword())) {
            // Password does not match
            $e->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
              ->setMessages(array('Supplied credential is invalid.'));
            $this->setSatisfied(false);
            return false;
        }

        // Success!
        $e->setIdentity($userEntity->getId());
        // Update user's password hash if the cost parameter has changed
        $this->updateUserPasswordHash($userEntity, $credential, $bcrypt);
        $this->setSatisfied(true);
        $storage = $this->getStorage()->read();
        $storage['identity'] = $e->getIdentity();
        $this->getStorage()->write($storage);
        $e->setCode(AuthenticationResult::SUCCESS)
          ->setMessages(array('Authentication successful.'));
    }

    protected function updateUserPasswordHash($userEntity, $password, $bcrypt)
    {
        $hash = explode('$', $userEntity->getPassword());
        if ($hash[2] === $bcrypt->getCost()) return;
        $userEntity->setPassword($bcrypt->create($password));
        $userEntity->save();
        return $this;
    }

    public function preprocessCredential($credential)
    {
        $processor = $this->getCredentialPreprocessor();
        if (is_callable($processor)) {
            return $processor($credential);
        }
        return $credential;
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
     * Get credentialPreprocessor.
     *
     * @return \callable
     */
    public function getCredentialPreprocessor()
    {
        return $this->credentialPreprocessor;
    }

    /**
     * Set credentialPreprocessor.
     *
     * @param $credentialPreprocessor the value to be set
     */
    public function setCredentialPreprocessor($credentialPreprocessor)
    {
        $this->credentialPreprocessor = $credentialPreprocessor;
        return $this;
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
