<?php

namespace Templates\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Installs Templates module
 */
class Installer implements ServiceManagerAwareInterface
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
    
    public function install()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $db = $this->serviceManager->get('db');
        
        $modules = $moduleManager->getActiveModules();     
                
        foreach ($modules as $moduleKey => $moduleConfig) {
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
                        values (?, ?, ?, ?, ?, ?)', array($template['name'], $template['title'], $template['type'], $moduleKey, $method, $isDefault));

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
    }
}