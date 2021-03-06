<?php

namespace AdminPanel\View\ResultComposer;

use App\View\ResultComposer\ComposerAbstract;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class JsonHtmlComposer extends ComposerAbstract
{    
    public function getResult(array $resultArray)
    {
        $viewRenderer = $this->serviceManager->get('ViewRenderer');
        
        if (!empty($resultArray['errors'])) {
            foreach ($resultArray['errors'] as $k => $v) {
                unset($resultArray['errors'][$k]['debug_backtrace']);
                unset($resultArray['errors'][$k]['err_context']);
            }
        }
        
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
        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));
        
        $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceManager->get('viewHelperManager')));

        $this->removeObjectsFromArray($resultArray);
        
        return new JsonModel($resultArray);
    }
}