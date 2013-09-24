<?php

namespace Users\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class UsersList implements ServiceManagerAwareInterface
{
    protected $serviceManager;    
    
    protected $table = 'users';
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getItems()
    {
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        $db = $this->serviceManager->get('db');
        
        $items = array();    
        
        $sqlRes = $db->query('
            select id, display_name, login, email
            from ' . DB_PREF . $this->table, array())->toArray();
        
        foreach ($sqlRes as $row) {
            $row['state'] = 'open';
            
            if ($row['display_name'] != '') {
                $row['name'] = $row['display_name'];
            } elseif ($row['login'] != '') {
                $row['name'] = $row['login'];
            } else {
                $row['name'] = $row['email'];
            }
            
            $row['state'] = 'open';
            $row['icons'] = array();
            
            $row['icons']['editLink'] = $urlPlugin->fromRoute('admin/method', array(
                'module' => 'Users',
                'method' => 'EditUser',
                'id' => $row['id']
            ));

            $row['icons']['delLink'] = 'zen.users.delUser(\'' . $urlPlugin->fromRoute('admin/method', array(
                'module' => 'Users',   
                'method' => 'DeleteUser',
            )) . '\', ' . $row['id'] . ')';
                        
            $items[] = $row;
        }
        
        return array(
            'items' => $items,
        );
    }
}