<?php

namespace ObjectTypes\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class GuidesList implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getGuides()
    {        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
                
        $result = array();
        
        $result['items'] = array();        
        
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $guides = $objectTypesCollection->getGuidesData();
               
        foreach ($guides as $id=>$row) {            
            $row['state'] = 'open';
            
            $row['icons'] = array(
                'showLink' => $urlPlugin->fromRoute('admin/GuideItemsList', array(
                    'id' => $row['id']
                )),
                'editLink' => $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',   
                    'method' => 'EditObjectType',
                    'id' => $row['id']
                )),
                'addLink' => $urlPlugin->fromRoute('admin', array(
                    'module' => 'ObjectTypes',   
                    'method' => 'AddObjectType',
                    'id' => $row['id'],
                )),
            );
            
            if (!$row['is_locked']) {
                $row['icons']['delLink'] = 'zen.objectTypes.delObjectType(\'' . $urlPlugin->fromRoute('admin', array(
                    'module' => 'ObjectTypes',   
                    'method' => 'DelObjectType',
                    'id' => $row['id']
                )) . '\')';
            }
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
}