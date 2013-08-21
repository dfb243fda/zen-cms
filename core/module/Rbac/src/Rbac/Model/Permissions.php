<?php

namespace Rbac\Model;

class Permissions
{
    protected $serviceManager;
    
    protected $db;
    
    protected $translator;
    
    protected $rolesTable = 'roles';
    
    protected $permissionsTable = 'role_permissions';
    
    protected $permissionResourcesTable = 'permission_resources';
    
    protected $roles;
    
    protected $permissions;
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        $this->db = $sm->get('db');
        $this->translator = $sm->get('translator');
        $this->moduleManager = $sm->get('moduleManager');
        $this->authService = $this->serviceManager->get('Rbac\Service\Authorize');
    }
    
    public function getRoles()
    {
        if (null === $this->roles) {
            $roles = array();
        
            $sqlRes = $this->db->query('select id, name from ' . DB_PREF . $this->rolesTable, array())->toArray();        
            foreach ($sqlRes as $row) {
                $roles[$row['id']] = $row['name'];
            }
            
            $this->roles = $roles;
        }
        return $this->roles;
    }
    
    public function getPermissions()
    {
        if (null === $this->permissions) {
            $roles = $this->getRoles();
            
            $sqlRes = $this->db->query('select resource, privelege, name, module from ' . DB_PREF . $this->permissionResourcesTable, array())->toArray();
        
            $permissions = array();        
            foreach ($sqlRes as $row) {
                $row['roles'] = array();
                foreach ($roles as $roleId => $roleName) {     
                    $tmpResource = $row['resource'];
                    if ('' == $tmpResource) {
                        $tmpResource = null;
                    }
                    $tmpPermission = $row['privelege'];
                    if ('' == $tmpPermission) {
                        $tmpPermission = null;
                    }

                    $isAllowed = (int)$this->authService->getAcl()->isAllowed('id_' . $roleId, $tmpResource, $tmpPermission);                
                    $row['roles'][$roleId] = $isAllowed;
                }

                $row['name'] = $this->translator->translateI18n($row['name']);      

                if (isset($permissions[$row['module']])) {
                    $permissions[$row['module']]['items'][$row['resource']][$row['privelege']] = $row;
                } else {
                    $moduleConfig = $this->moduleManager->getModuleConfig($row['module']);

                    $permissions[$row['module']] = array(
                        'name' => $moduleConfig['title'],
                        'items' => array(
                            $row['resource'] => array(
                                $row['privelege'] => $row,
                            ),
                        ),
                    );
                }
            }
            $this->permissions = $permissions;
        }
        return $this->permissions;
    }
    
    public function edit($roleId, $resource, $privelege, $isAllowed)
    {
        $result = array(
            'success' => false,
        );
        
        if ('' == $resource) {
            $resource = null;
        }
        if ('' == $privelege) {
            $privelege = null;
        }            

        if (!$this->authService->getAcl()->hasRole('id_' . $roleId)) {
            $result['errMsg'] = 'Переданы неверные параметры';
            return $result;
        }
        if (null !== $resource && !$this->authService->getAcl()->hasResource($resource)) {
            $result['errMsg'] = 'resource ' . $resource . ' does not exists';
            return $result;
        }

        if ($isAllowed && $this->authService->getAcl()->isAllowed('id_' . $roleId, $resource, $privelege)) {
            $result['errMsg'] = 'privelege is already allowed';
        } elseif (!$isAllowed && !$this->authService->getAcl()->isAllowed('id_' . $roleId, $resource, $privelege)) {
            $result['errMsg'] = 'privelege is already disallowed';
        } else {
            if (null === $resource) {
                $this->db->query('delete from ' . DB_PREF . $this->permissionsTable . ' where role = ?', array($roleId));
            } elseif (null === $privelege) {
                $this->db->query('delete from ' . DB_PREF . $this->permissionsTable . ' where role = ? and resource = ?', array($roleId, $resource));
            } else {
                $this->db->query('delete from ' . DB_PREF . $this->permissionsTable . ' where role = ? and resource = ? and privelege = ?', array($roleId, $resource, $privelege));
            }
            $this->db->query('insert into ' . DB_PREF . $this->permissionsTable . ' (role, resource, privelege, is_allowed) values(?, ?, ?, ?)', array($roleId, (string)$resource, (string)$privelege, $isAllowed));
            $result['success'] = true;
        }                  
        
        return $result;
    }
}