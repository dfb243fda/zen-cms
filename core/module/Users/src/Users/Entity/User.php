<?php

namespace Users\Entity;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Sql;

class User implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $userData = array();
    
    protected $objectTypeId;
    
    protected $usersTable = 'users';
    
    protected $userRoleLinkerTable = 'user_role_linker';
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->userData['id'];
    }

    /**
     * Set id.
     *
     * @param int $id
     * @return UserInterface
     */
    public function setId($id)
    {
        $this->userData['id'] = (int) $id;
        return $this;
    }

    /**
     * Get login.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->userData['login'];
    }

    /**
     * Set login.
     *
     * @param string $login
     * @return UserInterface
     */
    public function setLogin($login)
    {
        $this->userData['login'] = $login;
        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->userData['email'];
    }

    /**
     * Set email.
     *
     * @param string $email
     * @return UserInterface
     */
    public function setEmail($email)
    {
        $this->userData['email'] = $email;
        return $this;
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->userData['display_name'];
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     * @return UserInterface
     */
    public function setDisplayName($displayName)
    {
        $this->userData['display_name'] = $displayName;
        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->userData['password'];
    }

    /**
     * Set password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPassword($password)
    {
        $this->userData['password'] = $password;
        return $this;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->userData['state'];
    }

    /**
     * Set state.
     *
     * @param int $state
     * @return UserInterface
     */
    public function setState($state)
    {
        $this->userData['state'] = $state;
        return $this;
    }
    
    public function getObjectId()
    {
        return $this->userData['object_id'];
    }
    
    public function setObjectId($objectId)
    {
        $this->userData['object_id'] = $objectId;
        return $this;
    }
    
    public function getRoles()
    {
        return $this->userData['roles'];
    }
    
    public function setRoles($roles)
    {
        $this->userData['roles'] = $roles;
        return $this;
    }
    
    public function toArray()
    {
        return $this->userData;
    }
    
    public function setData($data)
    {
        $this->userData = $data;
        return $this;
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
                    ->setUserId($this->userData['id'])
                    ->setPopulateForm($populateForm);
        return $formFactory->getForm();
    }
    
    public function editUser($data)
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
        
        $userId = $this->userData['id'];
        
        $objectId = $this->userData['object_id'];
        $objectTypeId = $insertBase['object_type_id'];
        unset($insertBase['object_type_id']);

        $roles = $insertBase['roles'];
        unset($insertBase['roles']);

        $db->query('delete from ' . DB_PREF . $this->userRoleLinkerTable . ' where user_id = ?', array($userId));
        foreach ($roles as $roleId) {
            $db->query('insert into ' . DB_PREF . $this->userRoleLinkerTable . ' (user_id, role_id) values (?, ?)', array($userId, $roleId));
        }

        unset($insertBase['passwordVerify']);
        if ($insertBase['password'] == '') {
            unset($insertBase['password']);
        } else {
            $bcrypt = new Bcrypt;
            $bcrypt->setCost($usersConfig['passwordCost']);
            $password = $bcrypt->create($insertBase['password']);

            $insertBase['password'] = $password;
        }   
        

        $sql = new Sql($db);
        $update = $sql->update(DB_PREF . $this->usersTable)->set($insertBase)->where('id = ' . (int)$userId);
        $sql->prepareStatementForSqlObject($update)->execute();  
        
        
        $object = $objectsCollection->getObject($objectId);                
        $object->setName('user-item')->setTypeId($objectTypeId)->save();

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
        
        return true;
    }
}
