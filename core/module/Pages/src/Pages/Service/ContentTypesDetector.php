<?php

namespace Pages\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * It searchs content types in all modules and write them in page_types table
 */
class ContentTypesDetector implements ServiceManagerAwareInterface
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
        
        $pageContentTypes = array();
        
        foreach ($modules as $module => $moduleConfig) {
            if (!empty($moduleConfig['methods'])) {
                foreach ($moduleConfig['methods'] as $method=>$methodData) {
                    if (isset($methodData['type'])) {
                        if ('fe_content' == $methodData['type']) {
                            $pageContentTypes[] = array(
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
                
        foreach ($pageContentTypes as $v) {
            $db->query('
                insert ignore into ' . DB_PREF . 'page_content_types
                    (title, module, method, service)
                values (?, ?, ?, ?)', array($v['title'], $v['module'], $v['method'], $v['service']));
        }
    }
}