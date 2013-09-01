<?php

namespace AdminPanel\Service\OutputRenderer;

use AdminPanel\Service\OutputRendererAbstract;
use Zend\View\Model\ViewModel;

class JsonHtml extends OutputRendererAbstract
{    
    public function render(array $resultArray)
    {
        $response = $this->serviceManager->get('response');
        $eventManager = $this->serviceManager->get('application')->getEventManager();
        
        $response->getHeaders()->addHeaders(array('Content-Type' => 'application/json; charset=utf-8'));

        if (!empty($resultArray['errors'])) {
            foreach ($resultArray['errors'] as $k => $v) {
                unset($resultArray['errors'][$k]['debug_backtrace']);
                unset($resultArray['errors'][$k]['err_context']);
            }
        }

        $viewRender = $this->serviceManager->get('ViewRenderer');

        if (isset($resultArray['page']['contentTemplate'])) {                    
            $contentViewModel = new ViewModel();
            $contentViewModel->setTemplate($resultArray['page']['contentTemplate']['name']);
            if (isset($resultArray['page']['contentTemplate']['data'])) {
                $contentViewModel->setVariables($resultArray['page']['contentTemplate']['data']);
            } 

            $resultArray['page']['content'] = $viewRender->render($contentViewModel);
            unset($resultArray['page']['contentTemplate']);
        }

        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));

        $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceManager->get('viewHelperManager')));

        $this->removeObjectsFromArray($resultArray);
        
        return json_encode($resultArray);
    }
}