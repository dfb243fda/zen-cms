<?php

namespace FrontEnd\View\ResultComposer;

use App\View\ResultComposer\ComposerAbstract;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class JsonHtmlComposer extends ComposerAbstract
{    
    public function getResult(array $resultArray)
    {
        $viewRenderer = $this->serviceManager->get('ViewRenderer');
        $page = $this->target->getPageEntity();
        $eventManager = $this->serviceManager->get('application')->getEventManager();
        
        if (!empty($resultArray['errors'])) {
            foreach ($resultArray['errors'] as $k => $v) {
                unset($resultArray['errors'][$k]['debug_backtrace']);
                unset($resultArray['errors'][$k]['err_context']);
            }
        }
        
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
        
        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));
        
        $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceManager->get('viewHelperManager')));

        $this->removeObjectsFromArray($resultArray);
        
        return new JsonModel($resultArray);
    }
}