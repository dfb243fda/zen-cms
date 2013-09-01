<?php

namespace AdminPanel\Service\OutputRenderer;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use AdminPanel\Service\OutputRendererInterface;
use Zend\View\Model\ViewModel;

class Html implements 
    ServiceManagerAwareInterface,
    OutputRendererInterface
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
    
    public function render(array $resultArray)
    {
        $response = $this->serviceManager->get('response');
        $config = $this->serviceManager->get('config');
        $eventManager = $this->serviceManager->get('application')->getEventManager();
        
        $response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                
        $themePageTemplate = $config[CURRENT_THEME]['defaultTemplate'];

        $view = new ViewModel();     

        $viewRenderer = $this->serviceManager->get('ViewRenderer');

        if (isset($resultArray['page']['contentTemplate'])) {                    
            $contentViewModel = new ViewModel();
            $contentViewModel->setTemplate($resultArray['page']['contentTemplate']['name']);                    
            if (isset($resultArray['page']['contentTemplate']['data'])) {
                $contentViewModel->setVariables($resultArray['page']['contentTemplate']['data']);
            }                    
            $resultArray['page']['content'] = $viewRenderer->render($contentViewModel);
            unset($resultArray['page']['contentTemplate']);
        }

        $view->setVariables($resultArray);                
        $view->setTemplate($themePageTemplate);

        $layout = $this->serviceManager->get('ControllerPluginManager')->get('layout');

        $wrapperViewModel = $layout();

        $wrapperViewModel->content = $viewRenderer->render($view);

        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));

        return $viewRenderer->render($wrapperViewModel);  
    }
}