<?php

namespace DirectAccessToMethods\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use App\Method\MethodInterface;
use DirectAccessToMethods\Exception\DirectAccessException;

class DirectAccess implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $module;
    
    protected $method;
    
    protected $args = array();
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }
    
    public function setArgs($args)
    {
        $this->args = $args;
    }
    
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    
    public function getMethodResult()
    {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $methodManager = $this->serviceManager->get('methodManager');
        $translator = $this->serviceManager->get('translator');
        
        $module = $this->module;
        $method = $this->method;
        $args = $this->args;
        
        $result = array();
        
        if ($moduleManager->isModuleActive($module)) {
            if ($moduleManager->isMethodExists($module, $method)) {
                $moduleConfig = $moduleManager->getModuleConfig($module);

                if (isset($moduleConfig['methods'][$method]['directAccess']) && $moduleConfig['methods'][$method]['directAccess']) {
                    $serviceName = $moduleConfig['methods'][$method]['service'];

                    $instance = $methodManager->get($serviceName);
                    
                    if (!$instance instanceof MethodInterface) {
                        throw new DirectAccessException('The method ' . get_class($instance) . ' does not implements App\Method\MethodInterface');
                    }
                    
                    $instance->init();
                    
                    return call_user_func_array(array($instance, 'main'), $args);
                } else {
                    throw new DirectAccessException($translator->translate('This method does not support direct access'));
                }
            } else {
                 throw new DirectAccessException('Method ' . $method . ' is not exists in module ' . $module);                
            }
        } else {
            throw new DirectAccessException('Module ' . $module . ' is not active');
        }
    }
    
}