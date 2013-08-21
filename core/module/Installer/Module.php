<?php

namespace Installer;

use Zend\EventManager\EventInterface;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{    

    public function onBootstrap(EventInterface $event)
    {
        /* @var $app \Zend\Mvc\ApplicationInterface */
        $app            = $event->getTarget();
        /* @var $sm \Zend\ServiceManager\ServiceLocatorInterface */
        $serviceManager = $app->getServiceManager();
  
        $appConfig = $serviceManager->get('ApplicationConfig');
        
        if (!isset($appConfig['isInstalled']) || !$appConfig['isInstalled']) { 
            $request = $serviceManager->get('request');
            $sessionConfig = new \Zend\Session\Config\SessionConfig();
            $sessionConfig->setOptions(array(
                'cookie_path' => $request->getBasePath() . '/',
            ));
            $sessionManager = new \Zend\Session\SessionManager($sessionConfig);
            \Zend\Session\Container::setDefaultManager($sessionManager);
            $sessionManager->start();
            
        
            $event->getApplication()->getEventManager()->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($event) {  
                $error = $event->getError();
                
                if ('error-router-no-match' == $error) {
                    $response = $event->getResponse();
                    $router   = $event->getRouter();

                    $url = $router->assemble(array('action' => 'step1'), array('name' => 'install'));

                    $response->getHeaders()->addHeaderLine('Location', $url);
                    $response->setStatusCode(302);
                    
                    $event->setResponse($response);
                }
            }, -4000);
        }
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }   
}
