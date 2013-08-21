<?php

namespace DirectAccessToMethods\View\Helper\ExecuteMethod;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExecuteMethodFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $pluginManager)
    {        
        $serviceLocator = $pluginManager->getServiceLocator();
                
        $config = $serviceLocator->get('Config');        
        $tmpConfig = isset($config['executeMethod']) ? $config['executeMethod'] : array();
  //      $tmpConfig['serviceManager'] = $serviceLocator;        
        
        return new ExecuteMethod($tmpConfig);
    }
}
