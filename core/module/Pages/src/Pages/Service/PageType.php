<?php

namespace Pages\Service;

use Pages\Entity\FePageMethodInterface;

class PageType
{
    protected $serviceManager;
    
    protected $pageTypeId;
    
    protected $db;
    
    protected $pageTypesTable = 'page_types';
    
    public function __construct($sm, $pageTypeId)
    {        
        $this->serviceManager = $sm;
        
        $this->pageTypeId = $pageTypeId;
        
        $this->db = $sm->get('db');
        
        $this->moduleManager = $sm->get('moduleManager');
    }
    
    public function prepareResult(&$result)
    {
        if ($this->pageTypeId) {
            $sqlRes = $this->db->query('select module, method from ' . DB_PREF . $this->pageTypesTable . ' where id = ?', array($this->pageTypeId))->toArray();
            
            if (!empty($sqlRes)) {
                if ($this->moduleManager->isModuleActive($sqlRes[0]['module']) && $this->moduleManager->isMethodExists($sqlRes[0]['module'], $sqlRes[0]['method'])) {
                    $moduleConfig = $this->moduleManager->getModuleConfig($sqlRes[0]['module']);
                    
                    $serviceName = $moduleConfig['methods'][$sqlRes[0]['method']]['service'];

                    $methodManager = $this->serviceManager->get('methodManager');
                    
                    $instance = $methodManager->get($serviceName);
                    
                    if (!$instance instanceof FePageMethodInterface) {
                        throw new \Exception(get_class($instance) . ' does not implements \Pages\Entity\FePageMethodInterface');
                    }
                    
                    $instance->init();
                    
                    $instance->setPageData($result['page']);
                    
                    $tmpResult = (array)$instance->main();
                    if (is_array($tmpResult)) {
                        $result = array_merge($result, $tmpResult);
                    } else {
                        $result['page']['content'] = $tmpResult;
                    }                    
                }           
            }
        }
    }
}