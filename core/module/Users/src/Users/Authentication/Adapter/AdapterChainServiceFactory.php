<?php
namespace Users\Authentication\Adapter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Users\Authentication\Adapter\AdapterChain;
use Users\Options\ModuleOptions;
use Users\Authentication\Adapter\Exception\OptionsNotFoundException;

class AdapterChainServiceFactory implements FactoryInterface
{

    /**
     * @var ModuleOptions
     */
 //   protected $options;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $chain = new AdapterChain();
        
        $config = $serviceLocator->get('config');
        
        $options = $config['Users'];
        
        $authAdapters = $options['authAdapters'];
        
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