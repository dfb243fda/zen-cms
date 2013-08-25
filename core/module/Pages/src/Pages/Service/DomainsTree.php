<?php

namespace Pages\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Класс для отрисовки дерева страниц в админке
 */
class DomainsTree implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $domainsTable = 'domains';
        
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
    
    public function getDomains()
    {
        $result = array();
        
        $result['items'] = array();
        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $domains = $this->getDomainsData();
                
        foreach ($domains as $row) {            
            $row['icons'] = array(
                'editLink' => $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'Pages',   
                    'method' => 'EditDomain',
                    'id'     => $row['id']
                )),
                'delLink' => 'zen.pages.delDomain(\'' . $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'Pages',   
                    'method' => 'DeleteDomain',
                )) . '\', ' . $row['id'] . ')',
            );
            
            $result['items'][] = $row;
        }
                
        return $result;  
    }
    
    protected function getDomainsData()
    {
        $db = $this->serviceManager->get('db');
        
        $items = $db->query('
                    select id, host as name from ' . DB_PREF . $this->domainsTable . ' order by is_default desc
                    ', array())
                ->toArray();
        
        foreach ($items as $k=>$row) {
            $items[$k]['state'] = 'open';
        }
        
        return $items;
    }
}