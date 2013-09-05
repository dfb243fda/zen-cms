<?php

namespace Rbac\Collection;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Roles implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $roles;
    
    protected $rolesTable = 'roles';
    
    protected $userRoleLinkerTable = 'user_role_linker';    
    
    protected $rolePermissionsTable = 'role_permissions';
    
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
 
    public function getRoles()
    {
        if (null === $this->roles) {
            $roles = array();
            $db = $this->serviceManager->get('db');
        
            $sqlRes = $db->query('select id, name from ' . DB_PREF . $this->rolesTable, array())->toArray();        
            foreach ($sqlRes as $row) {
                $roles[$row['id']] = $row['name'];
            }
            
            $this->roles = $roles;
        }
        return $this->roles;
    }
    
    public function addRole($data)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select max(sorting) as max_sorting from ' . DB_PREF . $this->rolesTable, array())->toArray();
            
        $maxSorting = $sqlRes[0]['max_sorting'];
        if (null === $maxSorting) {
            $maxSorting = 0;
        }
        $sorting = $maxSorting + 1;

        $db->query('
            insert into ' . DB_PREF . $this->rolesTable . ' (name, parent, unauthorized, sorting)
            values (?, ?, ?, ?)
        ', array($data['name'], $data['parent'], $data['unauthorized'], $sorting));

        $roleId = $db->getDriver()->getLastGeneratedValue();
        
        return $roleId;
    }
    
    public function deleteRole($roleId)
    {
        $db = $this->serviceManager->get('db');
        
        $db->query('
            delete from ' . DB_PREF . $this->userRoleLinkerTable . '
            where role_id = ?
        ', array($roleId));
        
        $db->query('
            delete from ' . DB_PREF . $this->rolePermissionsTable . '
            where role = ?
        ', array($roleId));
        
        $db->query('
                delete from ' . DB_PREF . $this->rolesTable . '
                where id = ?
            ', array($roleId));
        
        return true;
    }
}