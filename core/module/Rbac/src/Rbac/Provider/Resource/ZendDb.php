<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Rbac\Provider\Resource;

/**
 * Array-based resources list
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
class ZendDb implements ProviderInterface
{
    /**
     * @var \Zend\Permissions\Acl\Resource\ResourceInterface[]
     */
    protected $resources = array();
    
    protected $tableName = 'permission_resources';

    /**
     * @param \Zend\Permissions\Acl\Resource\ResourceInterface[] $config
     */
    public function __construct(array $config = array(), $serviceManager)
    {
        $db = $serviceManager->get('db');
        
        $sqlRes = $db->query('select distinct resource from ' . DB_PREF . $this->tableName . ' where is_active = 1 and resource != ""', array())->toArray();
        
        $resources = array();
        foreach ($sqlRes as $row) {
            $resources[$row['resource']] = array();
        }
        
        $this->resources = $resources;
    }

    /**
     * {@inheritDoc}
     */
    public function getResources()
    {
        return $this->resources;
    }
}
