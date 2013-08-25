<?php

namespace Pages\Method;

use App\Method\AbstractMethod;
use Zend\View\Model\ViewModel;

class PageContent extends AbstractMethod
{        
    public function main($pageId = null, $templateId = null)
    {      
        if (null === $pageId || null === $templateId) {
            throw new \Exception('Wrong parameters transferred');
        }
        
        $pageContent = $this->serviceLocator->get('Pages\Service\PageContent');
        
        $pageContent->setPageId($pageId)
                      ->setTemplateId($templateId);
        
        $markers = $pageContent->getMarkers();
        
        $data = array(
            'markers' => $markers,
            'pageId' => $pageId,
        );
        
        $view = new ViewModel();         
        $view->setVariables($data);                
        $view->setTemplate('content_template/Pages/markers_blocks.phtml');
        
        $viewRender = $this->serviceLocator->get('ViewRenderer');      
        return $viewRender->render($view);    
    }
}