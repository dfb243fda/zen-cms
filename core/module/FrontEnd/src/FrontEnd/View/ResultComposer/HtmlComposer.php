<?php

namespace FrontEnd\View\ResultComposer;

use App\View\ResultComposer\ComposerAbstract;
use Zend\View\Model\ViewModel;

class HtmlComposer extends ComposerAbstract
{    
    public function getResult(array $resultArray)
    {
        $viewRenderer = $this->serviceManager->get('ViewRenderer');
        $page = $this->target->getPageEntity();
        $eventManager = $this->serviceManager->get('application')->getEventManager();
        
        if (!empty($resultArray['page']['content'])) {
            foreach ($resultArray['page']['content'] as $marker=>$content) {
                $str = '';
                foreach ($content as $v) {
                    $tmpTemplateData = $page->getTemplate($v['template']);

                    $tmpView = new ViewModel();
                    $tmpView->setTemplate($tmpTemplateData['type'] . '/' . $tmpTemplateData['module'] . '/' . $tmpTemplateData['method'] . '/' . $tmpTemplateData['name']);

                    $tmpView->setVariables($v);

                    $str .= $viewRenderer->render($tmpView);                            
                }

                $resultArray['page']['content'][$marker] = $str;
            }
        }

        $view = new ViewModel($resultArray);    
                   
        if (isset($resultArray['page']['template'])) {                    
            $tmpTemplateData = $page->getTemplate($resultArray['page']['template']);
            $view->setTemplate($tmpTemplateData['type'] . '/' . $tmpTemplateData['module'] . '/' . $tmpTemplateData['name']);
        } else {
            return $view;
        }
        
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