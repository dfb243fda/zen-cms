<?php

namespace Users\Collection;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Sql;

class Users implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $usersTable = 'users';
    
    protected $userRoleLinkerTable = 'user_role_linker';
    
    protected $objectTypeId;
    
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
            $data = $sqlRes[0];
            $data['roles'] = $this->getRoles($data['id']);
            $userEntity->setData($data);
            
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
            $data = $sqlRes[0];
            $data['roles'] = $this->getRoles($data['id']);
            $userEntity->setData($data);
            
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
            $data = $sqlRes[0];
            $data['roles'] = $this->getRoles($data['id']);
            $userEntity->setData($data);
            
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
            $data = $sqlRes[0];
            $data['roles'] = $this->getRoles($data['id']);
            $userEntity->setData($data);
            
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
    
    protected function getRoles($userId)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            select role_id from ' . DB_PREF . $this->userRoleLinkerTable . '
            where user_id = ?
        ', array($userId))->toArray();
        
        $roles = array();
        foreach ($sqlRes as $row) {
            $roles[] = $row['role_id'];
        }
        return $roles;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function getForm($populateForm)
    {
        $formFactory = $this->serviceManager->get('Users\FormFactory\UserFormFactory');
        $formFactory->setObjectTypeId($this->objectTypeId)
                    ->setPopulateForm($populateForm);
        return $formFactory->getForm();
    }
    
    public function addUser($data)
    {
        $insertFields = array();
        $insertBase = array();

        foreach ($data as $groupKey=>$groupData) {
            foreach ($groupData as $fieldName=>$fieldVal) {
                if ('field_' == substr($fieldName, 0, 6)) {
                    $insertFields[substr($fieldName, 6)] = $fieldVal;
                } else {
                    $insertBase[$fieldName] = $fieldVal;
                }
            }
        }
        
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $db = $this->serviceManager->get('db');
        $config = $this->serviceManager->get('config');
        $usersConfig = $config['Users'];
        
        unset($insertBase['passwordVerify']);
        $bcrypt = new Bcrypt;
        $bcrypt->setCost($usersConfig['passwordCost']);
        $password = $bcrypt->create($insertBase['password']);

        $insertBase['password'] = $password;
        
        $objectId = $objectsCollection->addObject('user-item', $insertBase['object_type_id']);
        $insertBase['object_id'] = $objectId;
        
        $objectTypeId = $insertBase['object_type_id'];
        unset($insertBase['object_type_id']);

        $roles = $insertBase['roles'];
        unset($insertBase['roles']);
        
        $sql = new Sql($db);
        $insert = $sql->insert(DB_PREF . $this->usersTable)->values($insertBase);
        $sql->prepareStatementForSqlObject($insert)->execute();    

        $userId = $db->getDriver()->getLastGeneratedValue();

        $db->query('
            delete from ' . DB_PREF . $this->userRoleLinkerTable . ' 
            where user_id = ?', array($userId));
        foreach ($roles as $roleId) {
            $db->query('
                insert into ' . DB_PREF . $this->userRoleLinkerTable . ' 
                    (user_id, role_id) 
                values (?, ?)', array($userId, $roleId));
        }
                
        
        $objectType = $objectTypesCollection->getType($objectTypeId);
        
        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (array_key_exists($k2, $insertFields)) {
                    $property = $objectPropertyCollection->getProperty($objectId, $k2); 
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }
        
        return $userId;
    }
}