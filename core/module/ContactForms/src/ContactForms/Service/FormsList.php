<?php

namespace ContactForms\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FormsList implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $formsTable = 'contact_forms';
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getData()
    {
        $db = $this->serviceManager->get('db');
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $result = array();
        
        $result['items'] = array();
        
        $sqlRes = $db->query('select * from ' . DB_PREF . $this->formsTable, array())->toArray();
        
        foreach ($sqlRes as $row) {
            $row['state'] = 'open';
            
            $row['icons'] = array(
                'editLink' => $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'ContactForms',   
                    'method' => 'EditForm',
                    'id'     => $row['id']
                )),
                'addLink' => $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'ContactForms',   
                    'method' => 'AddForm',
                )),
                'delLink' => 'zen.contact_forms.delForm(\'' . $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'ContactForms',   
                    'method' => 'DelForm',
                )) . '\', ' . $row['id'] . ')',
            );
            
            $result['items'][] = $row;
        }
        
        return $result;
    }
}