<?php

namespace ObjectTypes\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class GuideItemsList implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $guideId;
    
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
    
    public function setGuideId($guideId)
    {
        $this->guideId = $guideId;
        return $this;
    }
    
    public function getGuideItems()
    {
        $guideId = $this->guideId;
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $result = array();
        
        if (!$this->isGuidable()) {
            return array(
                'errMsg' => 'Не найден справочник ' . $guideId,
            );
        }
        
        $result['items'] = array();        
        
        $sqlRes = $db->query('select * from ' . DB_PREF . $this->objectsTable . ' where type_id = ? and is_deleted = 0', array($guideId))->toArray();
        
        $guideItems = array();        
        foreach ($sqlRes as $row) {
            $guideItems[$row['id']] = $row;
        }
        
        foreach ($guideItems as $row) {
            $row['state'] = 'open';
            
            $row['icons'] = array();
                        
            $row['icons']['editLink'] = $urlPlugin->fromRoute('admin/method', array(
                'module' => 'ObjectTypes',   
                'method' => 'EditGuideItem',
                'id' => $row['id']
            ));
            
            $row['icons']['delLink'] = 'zen.guides.delGuideItem(\'' . $urlPlugin->fromRoute('admin/method', array(
                'module' => 'ObjectTypes',   
                'method' => 'DelGuideItem',
            )) . '\', ' . $row['id'] . ')';
                
            $row['name'] = $translator->translateI18n($row['name']);
            
            $result['items'][] = $row;
        }
                
        return $result;
    }
    
    public function isGuidable()
    {        
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $objectType = $objectTypesCollection->getType($this->guideId);
        
        if ($objectType->getIsGuidable()) {
            return true;
        }
        return false;
    }
}