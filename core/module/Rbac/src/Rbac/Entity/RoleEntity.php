<?php

namespace Rbac\Entity;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class RoleEntity implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $roleId;
    
    protected $table = 'roles';
    
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
        return $this;
    }
    
    public function edit($data)
    {
        $db = $this->serviceManager->get('db');
        
        $db->query('
            update ' . DB_PREF . $this->table . '
                set name = ?, parent = ?, unauthorized = ?
            where id = ?
        ', array($data['name'], $data['parent'], $data['unauthorized'], $this->roleId));

        return true;        
    }
}