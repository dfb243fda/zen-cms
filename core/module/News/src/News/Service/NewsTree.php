<?php

namespace News\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class NewsTree implements ServiceManagerAwareInterface
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
        $newsService = $this->serviceManager->get('News\Service\News');
        
        $items = array();
        
        $typeIds = $newsService->getTypeIds();        
        
        $rubricTypeIds = $newsService->getRubricTypeIds();
        
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
            if (in_array($row['type_id'], $rubricTypeIds)) {
                $row['icons']['editLink'] = $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'News',
                    'method' => 'EditRubric',
                    'id' => $row['id']
                ));
            } else {
                $row['icons']['editLink'] = $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'News',
                    'method' => 'EditNews',
                    'id' => $row['id']
                ));
            }
            
            if (0 == $parentId) {
                $row['icons']['addLink'] = $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'News',
                    'method' => 'AddNews',
                    'id' => $row['id']
                ));
            }
            
            if (in_array($row['type_id'], $rubricTypeIds)) {
                $row['icons']['delLink'] = 'zen.news.delRubric(\'' . $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'News',   
                    'method' => 'DeleteRubric',
                )) . '\', ' . $row['id'] . ')';
            } else {
                $row['icons']['delLink'] = 'zen.news.delNews(\'' . $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'News',   
                    'method' => 'DeleteNews',
                )) . '\', ' . $row['id'] . ')';
            }
            
            $items[] = $row;
        }
               
        
        return array(
            'items' => $items,
        );
    }
}