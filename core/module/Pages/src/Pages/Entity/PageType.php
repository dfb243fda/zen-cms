<?php

namespace Pages\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Pages\AbstractMethod\FePageMethodInterface;

class PageType implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $pageTypeId;
    
    protected $pageTypesTable = 'page_types';
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setPageTypeId($typeId)
    {
        $this->pageTypeId = $typeId;
        return $this;
    }
    
    
    public function prepareResult(&$result)
    {
        $db = $this->serviceManager->get('db');
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $sqlRes = $db->query('select module, method from ' . DB_PREF . $this->pageTypesTable . ' where id = ?', array($this->pageTypeId))->toArray();
            
        if (!empty($sqlRes)) {
            if ($moduleManager->isModuleActive($sqlRes[0]['module']) && $moduleManager->isMethodExists($sqlRes[0]['module'], $sqlRes[0]['method'])) {
                $moduleConfig = $moduleManager->getModuleConfig($sqlRes[0]['module']);

                $serviceName = $moduleConfig['methods'][$sqlRes[0]['method']]['service'];

                $methodManager = $this->serviceManager->get('methodManager');

                $instance = $methodManager->get($serviceName);

                if (!$instance instanceof FePageMethodInterface) {
                    throw new \Exception(get_class($instance) . ' does not implements Pages\AbstractMethod\FePageMethodInterface');
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