<?php
namespace Users\Authentication\Adapter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Users\Authentication\Adapter\AdapterChain;

class AdapterChainServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $chain = new AdapterChain();
        
        $authAdaptersService = $serviceLocator->get('Users\Service\AuthenticationAdapters');
                
        $authAdapters = $authAdaptersService->getAdapters();
        
        //iterate and attach multiple adapters and events if offered
        foreach($authAdapters as $priority => $adapterName) {
            $adapter = $serviceLocator->get($adapterName);

            if(is_callable(array($adapter, 'authenticate'))) {
                $chain->getEventManager()->attach('authenticate', array($adapter, 'authenticate'), $priority);
            }

            if(is_callable(array($adapter, 'logout'))) {
                $chain->getEventManager()->attach('logout', array($adapter, 'logout'), $priority);
            }
        }
        
        return $chain;
    }

}