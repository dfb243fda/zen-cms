<?php

namespace DirectAccessToMethods\Service\OutputRenderer;

use DirectAccessToMethods\Service\OutputRendererAbstract;

class PrintR extends OutputRendererAbstract
{    
    public function render(array $resultArray)
    {
        $response = $this->serviceManager->get('response');
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
        
        return '<pre>' . print_r($resultArray, true) . '</pre>';
    }
}