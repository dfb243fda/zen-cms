<?php

namespace DirectAccessToMethods\Service\OutputRenderer;

use DirectAccessToMethods\Service\OutputRendererAbstract;

class Xml extends OutputRendererAbstract
{   
    public function render(array $resultArray)
    {
        $response = $this->serviceManager->get('response');
        $eventManager = $this->serviceManager->get('application')->getEventManager();
                        
        $response->getHeaders()->addHeaders(array('Content-Type' => 'text/xml; charset=utf-8'));

        if (!empty($resultArray['errors'])) {
            foreach ($resultArray['errors'] as $k => $v) {
                unset($resultArray['errors'][$k]['debug_backtrace']);
                unset($resultArray['errors'][$k]['err_context']);
            }
        }

        $eventManager->trigger('prepare_public_resources', $this, array($resultArray));

        $resultArray = array_merge($resultArray, $this->getViewResources($this->serviceManager->get('viewHelperManager')));

        $this->removeObjectsFromArray($resultArray);
        
        try {
            $xml = \Array2XML::createXML('result', $resultArray);
        } catch (\Exception $e) {
            $tmp = array('xmlError' => $e->getMessage());
            $xml = \Array2XML::createXML('result', $tmp);
        }                

        return $xml->saveXML();
    }
}