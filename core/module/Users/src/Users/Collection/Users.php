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
    
    protected $userRoleLinkerTable = 'user_role_linker';
    
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
    
    public function deleteUser($userId)
    {
        $db = $this->serviceManager->get('db');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        $db->query('
            delete from ' . DB_PREF . $this->userRoleLinkerTable . '
            where user_id = ?
        ', array($userId));
        
        $sqlRes = $db->query('
            select object_id 
            from ' . DB_PREF . $this->usersTable . '
            where id = ?
            ', array($userId))->toArray();
        
        
        if (empty($sqlRes)) {
            return false;
        } else {
            $objectsCollection->delObject($sqlRes[0]['object_id'], false);
            
            $db->query('
                    delete from ' . DB_PREF . $this->usersTable . '
                    where id = ?
                ', array($userId));
        }
        
        return true;
    }
}