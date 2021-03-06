<?php

namespace HtmlJsCssMinifier\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

class HtmlMinifier implements
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
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'minifyHtml'));
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
    
    public function minifyHtml(EventInterface $e)
    {
        $rendererStrategy = $this->serviceManager->get('App\View\RendererStrategy');       
            
        if ('html' == $rendererStrategy->getFormat()) {
            $configManager = $this->serviceManager->get('configManager');  

            if ($configManager->get('HtmlJsCssMinifier', 'minifyHtml')) {
                $htmlJsCssOptimizerService = $this->serviceManager->get('HtmlJsCssMinifier\Service\HtmlJsCssMinifier');
                $response = $e->getResponse();
                $html = $response->getBody(); // Maybe better getContent() ?                
                $html = $htmlJsCssOptimizerService->minifyHtml($html);  
                $response->setContent($html);
            }     
        }
    }    
}