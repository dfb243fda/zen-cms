<?php

namespace App\View;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use App\View\ResultComposer\ComposerInterface;
use App\View\ResultComposer\ComposerException;

class RendererStrategy implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $format;
    
    protected $target;
    
    protected $rendererStrategies = array(
        'json' => 'ViewJsonStrategy',
        'xml' => 'App\View\Strategy\XmlStrategy',
        'var_export' => 'App\View\Strategy\VarExportStrategy',
        'print_r' => 'App\View\Strategy\PrintRStrategy',    
    );
    
    protected $resultComposers = array(
        'json' => 'App\View\ResultComposer\JsonComposer',
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
    
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }
    
    public function getFormat()
    {
        return $this->format;
    }
    
    public function setRendererStrategies(array $strategies)
    {
        $this->rendererStrategies = $strategies;
        return $this;
    }
    
    public function getRendererStrategies()
    {
        return $this->rendererStrategies;
    }
    
    public function setResultComposers(array $composers)
    {
        $this->resultComposers = $composers;
        return $this;
    }
    
    public function getResultComposers()
    {
        return $this->resultComposers;
    }
    
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function registerStrategy()
    {
        $format = $this->format;
                
        if (isset($this->rendererStrategies[$format])) {
            $view = $this->serviceManager->get('Zend\View\View');
            $rendererStrategy = $this->serviceManager->get($this->rendererStrategies[$format]);
            $view->getEventManager()->attach($rendererStrategy, 100);
        }
    }
    
    public function getResult($resultArray)
    {
        $format = $this->format;
        if (isset($this->resultComposers[$format])) {            
            $view = $this->serviceManager->get('Zend\View\View');
            $resultComposer = $this->serviceManager->get($this->resultComposers[$format]);
            if (!$resultComposer instanceof ComposerInterface) {
                throw new ComposerException('result composer must implements App\View\ResultComposer\ComposerInterface');
            }
            $resultComposer->setTarget($this->target);
            return $resultComposer->getResult($resultArray);
        }
        return $resultArray;
    }    
    
    
    
    
    
    
}