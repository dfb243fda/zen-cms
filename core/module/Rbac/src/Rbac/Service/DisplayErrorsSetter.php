<?php

namespace Rbac\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class DisplayErrorsSetter implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setDisplayErrors()
    {
        $serviceManager = $this->serviceManager;
        
        $appConfig = $serviceManager->get('ApplicationConfig');
        
        $authService = $serviceManager->get('Rbac\Service\Authorize');
        if ($authService->isAllowed('get_errors') || true == $appConfig['show_errors_to_everybody']) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);         

            $serviceManager->get('viewManager')->getRouteNotFoundStrategy()->setDisplayExceptions(true);
            $serviceManager->get('viewManager')->getRouteNotFoundStrategy()->setDisplayNotFoundReason(true);

            $serviceManager->get('viewManager')->getExceptionStrategy()->setDisplayExceptions(true);

        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);     

            $serviceManager->get('viewManager')->getRouteNotFoundStrategy()->setDisplayExceptions(false);
            $serviceManager->get('viewManager')->getRouteNotFoundStrategy()->setDisplayNotFoundReason(false);

            $serviceManager->get('viewManager')->getExceptionStrategy()->setDisplayExceptions(false);
        }
    }
}