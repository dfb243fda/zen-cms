<?php

namespace DirectAccessToMethods\View;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class RendererStrategyOptions implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $format;
    
    protected $availableFormats = array(
        'json',
        'xml',
        'var_export',
        'print_r',
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

        $defaultFormat = 'json';
        
        if (null === $paramsPlugin->fromRoute('format')) {
            $format = $defaultFormat;
        }
        else {
            $format = (string)$paramsPlugin->fromRoute('format');
            if (!in_array($format, $this->availableFormats)) {
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
}