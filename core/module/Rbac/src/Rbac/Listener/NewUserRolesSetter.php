<?php

namespace Rbac\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\EventInterface;

class NewUserRolesSetter implements
    ListenerAggregateInterface,
    ServiceManagerAwareInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('user_registered.post', array($this, 'onUserRegistered'));
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function onUserRegistered(EventInterface $e)
    {
        $db = $this->serviceManager->get('db');
        $configManager = $this->serviceManager->get('configManager');
            
        $params = $e->getParams();

        $userId = $params['userId'];

        $roles = $configManager->get('users', 'new_user_roles');

        if (!empty($roles)) {
            foreach ($roles as $roleId) {
                $db->query('insert into ' . DB_PREF . 'user_role_linker (user_id, role_id) values (?, ?)', array($userId, $roleId));
            } 
        }
    }
}