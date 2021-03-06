<?php

namespace Users\View\ResultComposer;

use App\View\ResultComposer\ComposerAbstract;
use Zend\View\Model\ViewModel;

class HtmlComposer extends ComposerAbstract
{    
    public function getResult(array $resultArray)
    {
        $eventManager = $this->serviceManager->get('application')->getEventManager();
        $viewRenderer = $this->serviceManager->get('ViewRenderer');
                                
        $pageTemplate = $this->getTarget()->getHtmlTemplate();        

        $view = new ViewModel($resultArray);     
        $view->setTemplate($pageTemplate);
        
        // я не возвращаю view, потому что мне нужно отследить момент,
        // когда она отрисовывается, и вызвать событие prepare_public_resources
        // поэтому я отрисовыаю весь контент прямо здесь и возвращаю его
//        return $view;   
        
        $layout = $this->serviceManager->get('ControllerPluginManager')->get('layout');

        $wrapperViewModel = $layout();

        $wrapperViewModel->content = $viewRenderer->render($view);
        
        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));

        $response = $this->getTarget()->getResponse();
        $response->setContent($viewRenderer->render($wrapperViewModel));
        return $response;          
    }
}