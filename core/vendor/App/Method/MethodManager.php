<?php

namespace App\Method;

use Zend\ServiceManager\AbstractPluginManager;

//use Zend\ServiceManager\ConfigInterface;
//use Zend\ServiceManager\ServiceLocatorInterface;
//use Zend\ServiceManager\ServiceLocatorAwareInterface;

class MethodManager extends AbstractPluginManager
{
    protected $shareByDefault = false;
    
/*    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);
        // Pushing to bottom of stack to ensure this is done last
        $this->addInitializer(array($this, 'injectServiceManager'), false);
    }
    
    public function injectServiceManager($controller, ServiceLocatorInterface $serviceLocator)
    {
        if (!$controller instanceof MethodInterface) {
            return;
        }

        $parentLocator = $serviceLocator->getServiceLocator();

        if ($controller instanceof ServiceLocatorAwareInterface) {
            $controller->setServiceLocator($parentLocator->get('Zend\ServiceManager\ServiceLocatorInterface'));
        }
    }
 */   
     /**
     * Validate the plugin
     *
     * Checks that the helper loaded is an instance of Helper\HelperInterface.
     *
     * @param  mixed                            $plugin
     * @return void
     * @throws Exception\InvalidHelperException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof MethodInterface) {
            // we're okay
            return;
        }

        throw new \Exception(sprintf(
            'Plugin of type %s is invalid; must implement %s\MethodInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}