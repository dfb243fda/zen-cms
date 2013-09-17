<?php

namespace Menu\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class MenuTree implements ServiceManagerAwareInterface
{
    protected $serviceManager;    
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getItems($parentId)
    {
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        $db = $this->serviceManager->get('db');
        $menuService = $this->serviceManager->get('Menu\Service\Menu');
        
        $items = array();
        
        $typeIds = $menuService->getTypeIds();        
        
        $typeIdsStr = implode(', ', $typeIds);
        
        $sqlRes = $db->query('
                SELECT t1.*, 
                    (SELECT count(t2.id) FROM ' .DB_PREF . 'objects t2 WHERE t2.parent_id=t1.id and t2.is_deleted=0) AS children_cnt
                FROM ' . DB_PREF . 'objects t1 
                WHERE t1.type_id IN (' . $typeIdsStr . ') AND t1.parent_id = ? AND t1.is_deleted = 0
                ORDER BY t1.sorting    
                ', array($parentId))
                ->toArray();
        
        foreach ($sqlRes as $row) {
            if ($row['children_cnt'] > 0) {
                $row['state'] = 'closed';
            }
            else {
                $row['state'] = 'open';
            }        
            $row['icons'] = array();
            $row['icons']['editLink'] = $urlPlugin->fromRoute('admin/method', array(
                'module' => 'Menu',
                'method' => 'Edit',
                'id' => $row['id']
            ));
            if (0 == $parentId) {
                $row['icons']['addLink'] = $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'Menu',
                    'method' => 'AddMenuItem',
                    'id' => $row['id']
                ));
                $row['icons']['delLink'] = 'zen.menu.delMenu(\'' . $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'Menu',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            } else {
                $row['icons']['delLink'] = 'zen.menu.delMenuItem(\'' . $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'Menu',   
                    'method' => 'Delete',
                )) . '\', ' . $row['id'] . ')';
            }
            
            $items[] = $row;
        }
               
        
        return array(
            'items' => $items,
        );
    }
}