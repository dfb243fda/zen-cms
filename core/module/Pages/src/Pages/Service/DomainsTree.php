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
    
    public function getWrapper()
    {        
        $result = array();
        
        $this->serviceManager->get('viewHelperManager')->get('InlineScript')->appendFile(ROOT_URL_SEGMENT . '/js/Pages/pages.js');
        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $translator = $this->serviceManager->get('translator');
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . CURRENT_THEME . '/tree_grid.phtml',
            'data' => array(
                'createBtn' => array(
                    'text' => 'Добавить домен',
                    'link' => $urlPlugin->fromRoute('admin/method', array(
                        'module' => 'Pages',
                        'method' => 'AddDomain',
                    )),
                ),
                'url' => $urlPlugin->fromRoute('admin/DomainsList', array(
                    'task'   => 'get_data',
                )),  
                'columns' => array(
                    array(
                        array(                        
                            'title' => $translator->translate('Pages:Domain field'),
                            'field' => 'name',
                            'width' => '200',
                        ),
                        array(                        
                            'title' => '',
                            'field' => 'icons',
                            'width' => '200',
                        )
                    )                    
                ),
            ),            
        );
        
        return $result;
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