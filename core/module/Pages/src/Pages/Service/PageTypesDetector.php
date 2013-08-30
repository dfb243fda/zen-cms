<?php

namespace Pages\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * It searchs page types in all modules and write them in page_types table
 */
class PageTypesDetector implements ServiceManagerAwareInterface
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
    
    public function detect()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $db = $this->serviceManager->get('db');
        
        $modules = $moduleManager->getActiveModules();
        
        $pageTypes = array();
        
        foreach ($modules as $module => $moduleConfig) {
            if (!empty($moduleConfig['methods'])) {
                foreach ($moduleConfig['methods'] as $method=>$methodData) {
                    if (isset($methodData['type'])) {
                        if ('fe_page' == $methodData['type']) {
                            $pageTypes[] = array(
                                'title' => $methodData['title'],
                                'module' => $module,
                                'method' => $method,
                                'service' => $methodData['service'],
                            );
                        }                        
                    }
                }
            }
        }
                
        foreach ($pageTypes as $v) {
            $db->query('
                insert ignore into ' . DB_PREF . 'page_types
                    (title, module, method, service)
                values (?, ?, ?, ?)', array($v['title'], $v['module'], $v['method'], $v['service']));
        }
    }
}