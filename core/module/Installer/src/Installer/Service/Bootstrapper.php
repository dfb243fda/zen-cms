<?php

namespace Installer\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;

class Bootstrapper implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function bootstrap()
    {
        $appConfig = $this->serviceManager->get('ApplicationConfig');
        
        if (!isset($appConfig['isInstalled']) || !$appConfig['isInstalled']) { 
            $request = $this->serviceManager->get('request');
            $sessionConfig = new SessionConfig();
            $sessionConfig->setOptions(array(
                'cookie_path' => $request->getBasePath() . '/',
            ));
            $sessionManager = new SessionManager($sessionConfig);
            Container::setDefaultManager($sessionManager);
            $sessionManager->start();
                            
            $phpSettings = $appConfig['phpSettings'];
            foreach($phpSettings as $key => $value) {
                ini_set($key, $value);
            }                
            
            $eventManager = $this->serviceManager->get('application')->getEventManager();
        
            $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($event) {  
                $error = $event->getError();
                
                if ('error-router-no-match' == $error) {
                    $response = $event->getResponse();
                    $router   = $event->getRouter();

                    $url = $router->assemble(array('action' => 'step1'), array('name' => 'install'));

                    $response->getHeaders()->addHeaderLine('Location', $url);
                    $response->setStatusCode(302);
                    
        //            $event->setResponse($response);
                    
                    return $response;
                }
            }, -4000);
        }
    }
}