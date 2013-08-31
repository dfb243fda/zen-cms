<?php

namespace DirectAccessToMethods\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use App\Method\MethodInterface;
use DirectAccessToMethods\Exception\DirectAccessException;

class ExecuteMethod extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
        
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function __invoke($method, $args = array(), $template = null)
    {    
        $methodManager = $this->serviceLocator->getServiceLocator()->get('methodManager');
        
        $instance = $methodManager->get($method);   
        
        if (!$instance instanceof MethodInterface) {
            throw new DirectAccessException('The method ' . get_class($instance) . ' does not implements App\Method\MethodInterface');
        }
                
        $instance->init();
        
        $result = call_user_func_array(array($instance, 'main'), $args);
        
        if (null === $template) {
            return $result;
        }

        return $this->getView()->partial($template, $result);
    }
}