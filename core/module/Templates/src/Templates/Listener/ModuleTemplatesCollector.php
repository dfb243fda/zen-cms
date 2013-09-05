<?php

namespace Templates\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\EventInterface;

class ModuleTemplatesCollector implements
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

        $moduleManager = $this->serviceManager->get('moduleManager');

        $moduleConfig = $moduleManager->getModuleConfig($module);

        $db = $this->serviceManager->get('db');

        if (isset($moduleConfig['default_templates'])) {                
            foreach ($moduleConfig['default_templates'] as $template) { 
                if (isset($template['is_default'])) {
                    $isDefault = 1;
                } else {
                    $isDefault = 0;
                }
                if (isset($template['method'])) {
                    $method = $template['method'];
                } else {
                    $method = '';
                }

                $db->query('
                    insert ignore into ' . DB_PREF . 'templates
                        (name, title, type, module, method, is_default)
                    values (?, ?, ?, ?, ?, ?)', array($template['name'], $template['title'], $template['type'], $module, $method, $isDefault));

                if (!empty($template['markers'])) {
                    $templateId = $db->getDriver()->getLastGeneratedValue();

                    foreach ($template['markers'] as $marker) {
                        $db->query('
                            insert into ' . DB_PREF . 'template_markers
                                (name, title, template_id)
                            values (?, ?, ?)', array($marker['name'], $marker['title'], $templateId));
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

        $sqlRes = $db->query('select id from ' . DB_PREF . 'templates where module = ?', array($module))->toArray();

        foreach ($sqlRes as $row) {
            $db->query('delete from ' . DB_PREF . 'template_markers where template_id = ?', array($row['id']));
            $db->query('delete from ' . DB_PREF . 'templates where id = ?', array($row['id']));
        }
    }
}