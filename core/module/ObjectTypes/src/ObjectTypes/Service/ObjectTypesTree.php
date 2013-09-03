<?php

namespace ObjectTypes\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ObjectTypesTree implements ServiceManagerAwareInterface
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
    
    public function getObjectTypes($parentId)
    {
        $result = array();
        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $result['items'] = array();        
        
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectTypes = $objectTypesCollection->getChildrenTypesList($parentId);
        
        foreach ($objectTypes as $row) {
            if ($row['children_cnt'] > 0) {
                $row['state'] = 'closed';
            }
            else {
                $row['state'] = 'open';
            }
            
            $row['icons'] = array();
            
            if ($row['is_guidable']) {
                $row['icons']['showLink'] = $urlPlugin->fromRoute('admin/GuideItemsList', array(
                    'id' => $row['id']
                ));
            }
            
            $row['icons']['editLink'] = $urlPlugin->fromRoute('admin/method', array(
                'module' => 'ObjectTypes',   
                'method' => 'EditObjectType',
                'id' => $row['id']
            ));
            
            $row['icons']['addLink'] = $urlPlugin->fromRoute('admin/method', array(
                'module' => 'ObjectTypes',   
                'method' => 'AddObjectType',
                'id' => $row['id'],
            ));
            
            if (!$row['is_locked']) {
                $row['icons']['delLink'] = 'zen.objectTypes.delObjectType(\'' . $urlPlugin->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',   
                    'method' => 'DelObjectType',
                )) . '\', ' . $row['id'] . ')';
            }
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
}