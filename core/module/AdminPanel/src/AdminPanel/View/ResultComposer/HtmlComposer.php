<?php

namespace AdminPanel\View\ResultComposer;

use App\View\ResultComposer\ComposerAbstract;
use Zend\View\Model\ViewModel;

class HtmlComposer extends ComposerAbstract
{    
    public function getResult(array $resultArray)
    {
        $config = $this->serviceManager->get('config');
        $themePageTemplate = $config[CURRENT_THEME]['defaultTemplate'];
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
        
        $eventManager = $this->serviceManager->get('application')->getEventManager();
           
        
        $view = new ViewModel($resultArray);
        $view->setTemplate($themePageTemplate);
        
        // я не возвращаю view здесь, потому что мне нужно отследить момент,
        // когда она отрисовывается, поэтому я отрисовыаю её прямо здесь
//        return $view;      
        
        
        $layout = $this->serviceManager->get('ControllerPluginManager')->get('layout');

        $wrapperViewModel = $layout();

        $wrapperViewModel->content = $viewRenderer->render($view);

        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));

//        return $viewRenderer->render($wrapperViewModel); 
       
        return $view;
        
    }
}