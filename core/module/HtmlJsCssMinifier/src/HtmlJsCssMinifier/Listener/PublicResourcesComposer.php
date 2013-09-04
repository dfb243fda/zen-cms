<?php

namespace HtmlJsCssMinifier\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

class PublicResourcesComposer implements
    ListenerAggregateInterface,
    ServiceManagerAwareInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('prepare_public_resources', array($this, 'preparePublicResources'));
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function preparePublicResources(EventInterface $e)
    {
        $htmlJsCssOptimizerService = $this->serviceManager->get('HtmlJsCssMinifier\Service\HtmlJsCssMinifier');
        $configManager = $this->serviceManager->get('configManager');
        
        $htmlJsCssOptimizerService->prepareHeadLink($configManager->get('HtmlJsCssMinifier', 'minifyCss'));
        $htmlJsCssOptimizerService->prepareHeadScript($configManager->get('HtmlJsCssMinifier', 'minifyJs'));
        $htmlJsCssOptimizerService->prepareInlineScript($configManager->get('HtmlJsCssMinifier', 'minifyJs'));
    }    
}