<?php

namespace Rbac\Collection;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Permissions implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $permissions;
    
    protected $permissionResourcesTable = 'permission_resources';
    
    protected $permissionsTable = 'role_permissions';
    
//    protected $rolesPrefix = '';
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
 
    public function getPermissions()
    {
        if (null === $this->permissions) {
            $rolesCollection = $this->serviceManager->get('Rbac\Collection\Roles');
            $authService = $this->serviceManager->get('Rbac\Service\Authorize');
            $db = $this->serviceManager->get('db');
            $translator = $this->serviceManager->get('translator');
            $moduleManager = $this->serviceManager->get('moduleManager');            
            $roles = $rolesCollection->getRoles();
            
            $sqlRes = $db->query('
                select resource, privelege, name, module 
                from ' . DB_PREF . $this->permissionResourcesTable, array())->toArray();
        
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

                    $isAllowed = (int)$authService->getAcl()->isAllowed($roleId, $tmpResource, $tmpPermission);                
                    $row['roles'][$roleId] = $isAllowed;
                }

                $row['name'] = $translator->translateI18n($row['name']);      

                if (isset($permissions[$row['module']])) {
                    $permissions[$row['module']]['items'][$row['resource']][$row['privelege']] = $row;
                } else {
                    $moduleConfig = $moduleManager->getModuleConfig($row['module']);

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
        $authService = $this->serviceManager->get('Rbac\Service\Authorize');
        $db = $this->serviceManager->get('db');
        
        $result = array(
            'success' => false,
        );
        
        if ('' == $resource) {
            $resource = null;
        }
        if ('' == $privelege) {
            $privelege = null;
        }            

        if (!$authService->getAcl()->hasRole($roleId)) {
            $result['errMsg'] = 'Переданы неверные параметры';
            return $result;
        }
        if (null !== $resource && !$authService->getAcl()->hasResource($resource)) {
            $result['errMsg'] = 'resource ' . $resource . ' does not exists';
            return $result;
        }

        if ($isAllowed && $authService->getAcl()->isAllowed($roleId, $resource, $privelege)) {
            $result['errMsg'] = 'privelege is already allowed';
        } elseif (!$isAllowed && !$authService->getAcl()->isAllowed($roleId, $resource, $privelege)) {
            $result['errMsg'] = 'privelege is already disallowed';
        } else {
            if (null === $resource) {
                $db->query('
                    delete from ' . DB_PREF . $this->permissionsTable . ' 
                    where role = ?', array($roleId));
            } elseif (null === $privelege) {
                $db->query('
                    delete from ' . DB_PREF . $this->permissionsTable . '
                    where role = ? and resource = ?', array($roleId, $resource));
            } else {
                $db->query('
                    delete from ' . DB_PREF . $this->permissionsTable . ' 
                    where role = ? and resource = ? and privelege = ?', array($roleId, $resource, $privelege));
            }
            $db->query('
                insert into ' . DB_PREF . $this->permissionsTable . ' (
                    role, resource, privelege, is_allowed)
                values(?, ?, ?, ?)', array($roleId, (string)$resource, (string)$privelege, $isAllowed));
            $result['success'] = true;
        }                  
        
        return $result;
    }
}