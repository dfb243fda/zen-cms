<?php

namespace Rbac\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class RolesTree implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $table = 'roles';
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getItems($parentId)
    {
        $db = $this->serviceManager->get('db');
        $urlPlugin = $this->serviceManager->get('controllerPluginManager')->get('url');
        
        $sqlRes = $db->query('
            select t1.*, (select count(t2.id) from ' . DB_PREF . $this->table . ' t2 where t2.parent = t1.id) AS children_cnt
            from ' . DB_PREF . $this->table . ' t1 
            where t1.parent = ?', array($parentId))->toArray();
               
        foreach ($sqlRes as $row) {
            if ($row['children_cnt'] > 0) {
                $row['state'] = 'closed';
            } else {
                $row['state'] = 'open';
            }        
            
            $row['icons'] = array();
            $row['icons']['editLink'] = $urlPlugin->fromRoute('admin/method', array(
                'module' => 'Rbac',
                'method' => 'EditRole',
                'id' => $row['id']
            ));
            $row['icons']['addLink'] = $urlPlugin->fromRoute('admin/method', array(
                'module' => 'Rbac',
                'method' => 'AddRole',
                'id' => $row['id']
            ));
            $row['icons']['delLink'] = 'zen.roles.delRole(\'' . $urlPlugin->fromRoute('admin/method', array(
                'module' => 'Rbac',   
                'method' => 'DeleteRole',
            )) . '\', ' . $row['id'] . ')';     
            
            $items[] = $row;
        }
        
        return array(
            'items' => $items,
        );
    }
    
    
}