<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Rbac\Provider\Identity;

use Rbac\Exception\InvalidRoleException;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Sql;
use Zend\Permissions\Acl\Role\RoleInterface;
use ZfcUser\Service\User;

/**
 * Identity provider based on {@see \Zend\Db\Adapter\Adapter}
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
class ZfcUserZendDb2 implements ProviderInterface
{
    /**
     * @var User
     */
    protected $userService;

    /**
     * @var string|\Zend\Permissions\Acl\Role\RoleInterface
     */
    protected $defaultRole;

    /**
     * @var string
     */
    protected $tableName = 'user_role_linker';

    protected $rolesTable = 'roles';
    
    /**
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @param \ZfcUser\Service\User    $userService
     */
    public function __construct(Adapter $adapter, User $userService)
    {
        $this->adapter     = $adapter;
        $this->userService = $userService;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentityRoles()
    {
        $authService = $this->userService->getAuthService();

        $roles     = array();
        
        if ( ! $authService->hasIdentity()) {
            $sqlRes = $this->adapter->query('select id from ' . DB_PREF . $this->rolesTable . ' where unauthorized = 1', array())->toArray();
            
            foreach ($sqlRes as $row) {
                $roles[] = 'id_' . $row['id'];
            }
            
            return $roles;
        }

        // get roles associated with the logged in user
        $sql    = new Sql($this->adapter);
        $select = $sql->select()->from(DB_PREF . $this->tableName);
        $where  = new Where();

        $where->equalTo('user_id', $authService->getIdentity()->getId());

        $results = $sql->prepareStatementForSqlObject($select->where($where))->execute();
        

        foreach ($results as $i) {
            $roles[] = 'id_' . $i['role_id'];
        }

        return $roles;
    }
}
