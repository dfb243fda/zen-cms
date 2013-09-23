<?php

namespace Search\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class SearchObjectTypes implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function refreshObjectTypes()
    {
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $db = $this->serviceManager->get('db');
        
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $modules = $moduleManager->getActiveModules();
        
        $db->query('TRUNCATE TABLE ' . DB_PREF . 'search_object_types', array());
        
        foreach ($modules as $module => $moduleConfig) {            
            if (!empty($moduleConfig['search_object_types'])) {                
                foreach ($moduleConfig['search_object_types'] as $value) {
                    if (isset($value['guid'])) {
                        $guid = $value['guid'];
                        
                        $typeId = $objectTypesCollection->getTypeIdByGuid($guid);
                        
                        $typeIds = array();
                        if (null !== $typeId) {
                            $typeIds[] = $typeId;
                            
                            if (isset($value['with_descendants']) && $value['with_descendants']) {
                                $descendantIds = $objectTypesCollection->getDescendantTypeIds($typeId);
                                
                                $typeIds = array_merge($typeIds, $descendantIds);
                            }
                        }
                        
                        if (!empty($typeIds)) {
                            foreach ($typeIds as $id) {
                                $db->query('insert ignore into ' . DB_PREF . 'search_object_types
                                    (guid, object_type_id, module)
                                    values (?, ?, ?)', array($guid, $id, $module));
                            }
                        }
                    }
                }
            }
        }
    }
}