<?php

namespace Users\View;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class RegistrationRendererStrategyOptions implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $format;
    
    protected $availableFormats = array(
        'json',
        'html',
        'xml',
        'var_export',
        'print_r',
    );
    
    protected $rendererStrategies = array(
        'json' => 'ViewJsonStrategy',
        'xml' => 'App\View\Strategy\XmlStrategy',
        'var_export' => 'App\View\Strategy\VarExportStrategy',
        'print_r' => 'App\View\Strategy\PrintRStrategy',   
    );
    
    protected $resultComposers = array(
        'json' => 'App\View\ResultComposer\JsonComposer',
        'html' => 'Users\View\ResultComposer\RegistrationHtmlComposer',
        'xml' => 'App\View\ResultComposer\XmlComposer',
        'var_export' => 'App\View\ResultComposer\VarExportComposer',
        'print_r' => 'App\View\ResultComposer\PrintRComposer',
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
            $defaultFormat = 'json';
        }
        else {
            $defaultFormat = 'html';
        }
        
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
    
    public function getRendererStrategies()
    {
        return $this->rendererStrategies;
    }
    
    public function getResultComposers()
    {
        return $this->resultComposers;
    }
}