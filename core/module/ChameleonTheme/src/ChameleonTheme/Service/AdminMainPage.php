<?php

namespace ChameleonTheme\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class AdminMainPage implements ServiceManagerAwareInterface
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
    
    public function getModulesList()
    {
        $translator = $this->serviceManager->get('translator');
                
        $moduleManager = $this->serviceManager->get('moduleManager');
        $modulesList = $moduleManager->getActiveModules();

        foreach ($modulesList as $k => $v) {
            $modulesList[$k]['title'] = $translator->translateI18n($v['title']);
            $modulesList[$k]['description'] = $translator->translateI18n($v['description']);    
            if (!empty($modulesList[$k]['methods'])) {
                foreach ($modulesList[$k]['methods'] as $k2 => $v2) {
                    $modulesList[$k]['methods'][$k2]['title'] = $translator->translateI18n($v2['title']);
                    $modulesList[$k]['methods'][$k2]['description'] = $translator->translateI18n($v2['description']);
                }
            }
        }
        
        return $modulesList;
    }
}