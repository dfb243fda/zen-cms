<?php

namespace AdminPanel\Service\OutputRenderer;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use AdminPanel\Service\OutputRendererInterface;
use Zend\View\Model\ViewModel;

class Xml implements 
    ServiceManagerAwareInterface,
    OutputRendererInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
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

        try {
            $xml = \Array2XML::createXML('result', $resultArray);
        } catch (\Exception $e) {
            $tmp = array('xmlError' => $e->getMessage());
            $xml = \Array2XML::createXML('result', $tmp);
        }                

        return $xml->saveXML();
    }
    
    protected function getViewResources($viewHelperManager)
    {
        $headScript = $viewHelperManager->get('headScript')->getContainer()->getValue();        
        if (is_object($headScript)) {
            $headScript = array($headScript);
        }
        
        $headLink = $viewHelperManager->get('headLink')->getContainer()->getValue();
        if (is_object($headLink)) {
            $headLink = array($headLink);
        }
        
        $inlineScript = $viewHelperManager->get('inlineScript')->getContainer()->getValue();
        if (is_object($inlineScript)) {
            $inlineScript = array($inlineScript);
        }
        
        $result = array();
        if (!empty($headScript)) {
            $result['headScript'] = $headScript;
        }
        if (!empty($headLink)) {
            $result['headLink'] = $headLink;
        }
        if (!empty($inlineScript)) {
            $result['inlineScript'] = $inlineScript;
        }
        
        return $result;
    }
}