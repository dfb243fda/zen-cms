<?php

namespace Pages\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Класс для отрисовки дерева страниц в админке
 */
class PagesTree implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $domainsTable = 'domains';
    
    protected $pagesTable = 'pages';
    
    protected $objectsTable = 'objects';
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getPages($parentId)
    {
        $result = array();
        
        $result['items'] = array();
        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $pages = $this->getPagesData($parentId);        
                
        foreach ($pages as $row) {            
            $row['icons'] = array(
                'editLink' => $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'Pages',   
                    'method' => 'EditPage',
                    'id'     => $row['id']
                )),
                'addLink' => $urlPlugin->fromRoute('admin/AddPage', array(
                    'pageId'     => $row['id']
                )),
                'delLink' => 'zen.pages.delPage(\'' . $urlPlugin->fromRoute('direct', array(
                    'module' => 'Pages',   
                    'method' => 'DeletePage',
                )) . '\', ' . $row['id'] . ')',
            );
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
    
    protected function getPagesData($parentId)
    {
        $items = array();
        
        $db = $this->serviceManager->get('db');
        
        $pages = $db->query('
                    SELECT p1.*,
                        (SELECT count(p2.id) FROM ' . DB_PREF . $this->pagesTable . ' p2 WHERE p2.parent_id = p1.id AND is_deleted=0) AS children_cnt,
                        (SELECT o.name FROM ' . DB_PREF . $this->objectsTable . ' o WHERE o.id=p1.object_id) AS name
                    FROM ' . DB_PREF . $this->pagesTable . ' p1
                    WHERE p1.parent_id = ? AND p1.is_deleted=0
                    ORDER BY p1.sorting
                    ', array($parentId))
                ->toArray();
        
        foreach ($pages as $row) {
            if ($row['children_cnt'] > 0) {
                $row['state'] = 'closed';
            }
            else {
                $row['state'] = 'open';
            }
            $items[] = $row;
        }
        
        return $items;
    }
    
    public function getDomains()
    {
        $result = array();
        
        $result['items'] = array();
        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
                        
        $domains = $this->getDomainsData();
        
        foreach ($domains as $row) {  
            
            if (!empty($row['children'])) {
                foreach ($row['children'] as $k=>$row2) {
                    $row['children'][$k]['icons'] = array(
                        'editLink' => $urlPlugin->fromRoute('admin/method', array(
                            'module' => 'Pages',   
                            'method' => 'EditPage',
                            'id' => $row2['id']
                        )),
                        'addLink' => $urlPlugin->fromRoute('admin/AddPage', array(
                            'pageId' => $row2['id']
                        )),
                        'delLink' => 'zen.pages.delPage(\'' . $urlPlugin->fromRoute('direct', array(
                            'module' => 'Pages',   
                            'method' => 'DeletePage',
                        )) . '\', ' . $row2['id'] . ')',
                    );
                }
            }
            
            
            $row['icons'] = array(
                'addLink' => $urlPlugin->fromRoute('admin/AddPage', array(
                    'domainId' => $row['domain_id'],
                )),
            );
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
    
    protected function getDomainsData()
    {
        $items = array();
        
        $db = $this->serviceManager->get('db');
        
        $domains = $db->query('SELECT *, host AS name FROM ' . DB_PREF . $this->domainsTable, array())->toArray();
        
        foreach ($domains as $row) {                    
            $pages = $db->query('
                        SELECT p1.*,
                            (SELECT count(p2.id) FROM ' . DB_PREF . $this->pagesTable . ' p2 WHERE p2.parent_id = p1.id AND is_deleted=0) AS children_cnt,
                            (SELECT o.name FROM ' . DB_PREF . $this->objectsTable . ' o WHERE o.id=p1.object_id) AS name
                        FROM ' . DB_PREF . $this->pagesTable . ' p1
                        WHERE p1.parent_id = 0 AND p1.domain_id=? AND p1.is_deleted=0
                        ORDER BY p1.sorting
                        ', array($row['id']))
                    ->toArray();
            
            if (empty($pages)) {
                $row['state'] = 'open';
            } else {
                $row['state'] = 'closed';
            }
            
            foreach ($pages as $row2) {
                if ($row2['children_cnt'] > 0) {
                    $row2['state'] = 'closed';
                }
                else {
                    $row2['state'] = 'open';
                }
                
                $row['children'][] = $row2;
            }
            
            $row['domain_id'] = $row['id'];
            $row['id'] = 'domain_' . $row['id'];
            $items[] = $row;
        }
        
        return $items;
    }
}