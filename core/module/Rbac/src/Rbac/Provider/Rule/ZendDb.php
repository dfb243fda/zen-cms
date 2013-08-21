<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Rbac\Provider\Rule;

/**
 * Rule provider based on a given array of rules
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
class ZendDb implements ProviderInterface
{
    /**
     * @var array
     */
    protected $rules = array();
    
    protected $tableName = 'role_permissions';

    /**
     * @param array $config
     */
    public function __construct(array $config = array(), $serviceManager)
    {
        $db = $serviceManager->get('db');
        
        $sqlRes = $db->query('select privelege, resource, role, is_allowed from ' . DB_PREF . $this->tableName, array())->toArray();
        
        $groupData = array();
        foreach ($sqlRes as $row) {
            if (isset($groupData[$row['privelege'] . ':' . $row['resource'] . ':' . $row['is_allowed']])) {
                $groupData[$row['privelege'] . ':' . $row['resource'] . ':' . $row['is_allowed']]['roles'][] = 'id_' . $row['role'];
            } else {
                $groupData[$row['privelege'] . ':' . $row['resource'] . ':' . $row['is_allowed']] = array(
                    'privelege' => $row['privelege'],
                    'resource' => $row['resource'],
                    'roles' => array('id_' . $row['role']),
                    'is_allowed' => $row['is_allowed'],
                );
            }
        }
        
        $rules = array(
            'allow' => array(),
            'deny' => array(),
        );
        foreach ($groupData as $row) {
            if ($row['is_allowed']) {
                $type = 'allow';
            } else {
                $type = 'deny';
            }
            if ('' == $row['resource']) {
                $rules[$type][] = array($row['roles'], null);
            } elseif ('' == $row['privelege']) {
                $rules[$type][] = array($row['roles'], $row['resource']);
            } else {
                $rules[$type][] = array($row['roles'], $row['resource'], $row['privelege']);
            }            
        }     
        
        $this->rules = $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function getRules()
    {
        return $this->rules;
    }
}
