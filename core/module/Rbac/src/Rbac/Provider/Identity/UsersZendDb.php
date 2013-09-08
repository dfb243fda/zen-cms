<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Rbac\Provider\Identity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Identity provider based on {@see \Zend\Db\Adapter\Adapter}
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
class UsersZendDb implements ProviderInterface, ServiceManagerAwareInterface
{
    /**
     * @var string
     */
    protected $userRoleLinkerTable = 'user_role_linker';

    protected $rolesTable = 'roles';
    
    protected $roles;
    
//    protected $rolesPrefix = '';
    
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentityRoles()
    {
        if ($this->roles !== null) {
            return $this->roles;
        }
        
        $authService = $this->serviceManager->get('users_auth_service');
        $db = $this->serviceManager->get('db');

        $roles = array();
        
        if ( ! $authService->hasIdentity()) {
            $sqlRes = $db->query('
                select id 
                from ' . DB_PREF . $this->rolesTable . ' 
                where unauthorized = 1', array())->toArray();
            
            foreach ($sqlRes as $row) {
                $roles[] = $row['id'];
            }
        } else {            
            $sqlRes = $db->query('
                select role_id
                from ' . DB_PREF . $this->userRoleLinkerTable . ' 
                    where user_id = ?', array($authService->getIdentity()))->toArray();
            
            foreach ($sqlRes as $row) {
                $roles[] = $row['role_id'];
            }
        }

        $this->roles = $roles;

        return $roles;
    }
}
