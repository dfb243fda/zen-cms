<?php

namespace Pages\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Mvc\MvcEvent;

class OnBootstrap implements
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
    
    public function onModuleInstalled(MvcEvent $e)
    {
        $params = $e->getParams();
            
        $module = $params['module'];

        $moduleConfig = $e->getTarget()->getModuleConfig($module);

        if (!empty($moduleConfig['methods'])) {
            $db = $this->serviceManager->get('db');
            foreach ($moduleConfig['methods'] as $k=>$v) {
                if (isset($v['type'])) {
                    if ('fe_page' == $v['type']) {
                        $db->query('
                            insert ignore into ' . DB_PREF . 'page_types 
                                (title, module, method, service) 
                            values (?, ?, ?, ?)', array($v['title'], $module, $k, $v['service']));
                    }

                    if ('fe_content' == $v['type']) {
                        $db->query('
                            insert ignore into ' . DB_PREF . 'page_content_types 
                                (title, module, method, service) 
                            values (?, ?, ?, ?)', array($v['title'], $module, $k, $v['service']));
                    }
                }
            }
        }
    }
    
    public function onModuleUninstalled(MvcEvent $e)
    {
        $params = $e->getParams();
   
        $module = $params['module'];

        $db = $this->serviceManager->get('db');

        $db->query('
            delete from ' . DB_PREF . 'page_types 
            where module = ?', array($module));

        $db->query('
            delete from ' . DB_PREF . 'page_content_types 
            where module = ?', array($module));
    }
}