<?php

namespace Search\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\EventInterface;

class ModuleSearchObjectTypesCollector implements
    ListenerAggregateInterface,
    ServiceManagerAwareInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('module_installed', array($this, 'onModuleInstalled'));
        $this->listeners[] = $events->attach('module_uninstalled', array($this, 'onModuleUninstalled'));
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function onModuleInstalled(EventInterface $e)
    {
        $params = $e->getParams();
            
        $module = $params['module'];

        $moduleConfig = $e->getTarget()->getModuleConfig($module);

        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');

        $db = $this->serviceManager->get('db');

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
    
    public function onModuleUninstalled(EventInterface $e)
    {
        $params = $e->getParams();
   
        $module = $params['module'];

        $db = $this->serviceManager->get('db');

        $db->query('delete from ' . DB_PREF . 'search_object_types
            where module = ?', array($module));
    }
}