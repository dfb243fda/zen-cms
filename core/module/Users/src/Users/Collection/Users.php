<?php

namespace Users\Collection;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Users implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $usersTable = 'users';
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getUserById($userId)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            select *
            from ' . DB_PREF . $this->usersTable . '
            where id = ?
        ', array($userId))->toArray();
        
        if (!empty($sqlRes)) {
            $userEntity = $this->serviceManager->get('Users\Entity\User');
            $userEntity->setData($sqlRes[0]);
            
            return $userEntity;
        }
        return null;
    }
    
    public function getUserByLogin($login)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            select *
            from ' . DB_PREF . $this->usersTable . '
            where login = ?
        ', array($login))->toArray();
        
        if (!empty($sqlRes)) {
            $userEntity = $this->serviceManager->get('Users\Entity\User');
            $userEntity->setData($sqlRes[0]);
            
            return $userEntity;
        }
        return null;
    }
    
    public function getUserByEmail($email)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            select *
            from ' . DB_PREF . $this->usersTable . '
            where email = ?
        ', array($email))->toArray();
        
        if (!empty($sqlRes)) {
            $userEntity = $this->serviceManager->get('Users\Entity\User');
            $userEntity->setData($sqlRes[0]);
            
            return $userEntity;
        }
        return null;
    }
    
    
    public function getUserByLoginzaId($loginzaId)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            select *
            from ' . DB_PREF . $this->usersTable . '
            where loginza_id = ?
        ', array($loginzaId))->toArray();
        
        if (!empty($sqlRes)) {
            $userEntity = $this->serviceManager->get('Users\Entity\User');
            $userEntity->setData($sqlRes[0]);
            
            return $userEntity;
        }
        return null;
    }
}