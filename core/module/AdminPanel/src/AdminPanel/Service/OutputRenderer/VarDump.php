<?php

namespace AdminPanel\Service\OutputRenderer;

use AdminPanel\Service\OutputRendererAbstract;

class VarDump extends OutputRendererAbstract
{    
    public function render(array $resultArray)
    {
        $response = $this->serviceManager->get('response');
        $config = $this->serviceManager->get('config');
        $eventManager = $this->serviceManager->get('application')->getEventManager();
        
        $response->getHeaders()->addHeaders(array('Content-Type' => 'text/html; charset=utf-8'));
                
        if (!empty($resultArray['errors'])) {
            foreach ($resultArray['errors'] as $k => $v) {
                unset($resultArray['errors'][$k]['debug_backtrace']);
                unset($resultArray['errors'][$k]['err_context']);
            }
        }

        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));

        $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceManager->get('viewHelperManager')));

        $this->removeObjectsFromArray($resultArray);
        
        return '<pre>' . var_export($resultArray, true) . '</pre>';
    }
}