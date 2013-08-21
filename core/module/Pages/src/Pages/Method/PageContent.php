<?php

namespace Pages\Method;

use App\Method\AbstractMethod;
use Zend\View\Model\ViewModel;

class PageContent extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $contentModel;
    
    protected $request;
    
    public function init()
    {        
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->contentModel = new \Pages\Model\PagesContent($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main($pageId = null, $templateId = null)
    {      
        if (null === $pageId || null === $templateId) {
            throw new \Exception('Wrong parameters transferred');
        }
        
        $markers = $this->contentModel->getMarkers($pageId, $templateId);
        
        $data = array(
            'markers' => $markers,
            'pageId' => $pageId,
        );
        
        $view = new ViewModel();         
        $view->setVariables($data);                
        $view->setTemplate('content_template/Pages/markers_blocks.phtml');
        
        $viewRender = $this->rootServiceLocator->get('ViewRenderer');      
        return $viewRender->render($view);    
    }
}