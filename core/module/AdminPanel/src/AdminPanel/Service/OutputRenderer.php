<?php

namespace AdminPanel\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class OutputRenderer implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $format;
    
    protected $outputRenderers = array(
        'json' => 'AdminPanel\Service\OutputRenderer\Json',
        'html' => 'AdminPanel\Service\OutputRenderer\Html',
        'xml' => 'AdminPanel\Service\OutputRenderer\Xml',
        'var_dump' => 'AdminPanel\Service\OutputRenderer\VarDump',
        'print_r' => 'AdminPanel\Service\OutputRenderer\PrintR', 
        'json_html' => 'AdminPanel\Service\OutputRenderer\JsonHtml',
    );
    
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    protected function detectFormat()
    {
        $request = $this->serviceManager->get('request');
        $paramsPlugin = $this->serviceManager->get('ControllerPluginManager')->get('params');
        
        if ($request->isXmlHttpRequest()) {
            $defaultFormat = 'json_html';
        }
        else {
            $defaultFormat = 'html';
        }
        
        if (null === $paramsPlugin->fromRoute('format')) {
            $format = $defaultFormat;
        }
        else {
            $format = (string)$paramsPlugin->fromRoute('format');
            if (!isset($this->outputRenderers[$format])) {
                $format = $defaultFormat;
            }
        }     
        
        $this->format = $format;
    }
    
    public function getFormat()
    {
        if (!$this->format) {
            $this->detectFormat();
        }
        return $this->format;
    }
    
    public function getOutput($resultArray)
    {
        $format = $this->getFormat();
        if (isset($this->outputRenderers[$format])) {
            $renderer = $this->serviceManager->get($this->outputRenderers[$format]);
            if (!$renderer instanceof OutputRendererInterface) {
                throw new OutputRenderer\Exception('Output renderer must implements AdminPanel\Service\OutputRendererInterface');
            }
            return $renderer->render($resultArray);
        } else {
            throw new OutputRenderer\Exception('unknown format ' . $format);  
        }
    } 
}