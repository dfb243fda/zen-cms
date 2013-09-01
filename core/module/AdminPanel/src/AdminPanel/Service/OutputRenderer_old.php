<?php


/**
 * Предлагаю переименовать класс RendereringStrategy
 * создать методы getResult и registerStrategy
 */

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
        'json' => 'App\View\Strategy\JsonStrategy',
        'html' => 'AdminPanel\Service\OutputRenderer\Html',
        
/*        'json' => 'AdminPanel\Service\OutputRenderer\Json',
        'html' => 'AdminPanel\Service\OutputRenderer\Html',
        'xml' => 'AdminPanel\Service\OutputRenderer\Xml',
        'var_dump' => 'AdminPanel\Service\OutputRenderer\VarDump',
        'print_r' => 'AdminPanel\Service\OutputRenderer\PrintR', 
        'json_html' => 'AdminPanel\Service\OutputRenderer\JsonHtml',
 * 
 */
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
    
    public function registerJsonStrategy($e)
    {
        $view         = $this->serviceManager->get('Zend\View\View');
        $jsonStrategy = $this->serviceManager->get('ViewJsonStrategy');
                
        $view->getEventManager()->attach($jsonStrategy, 100);
    }
    
    public function setRenderingStrategy()
    {
        $format = $this->getFormat();
        if (isset($this->outputRenderers[$format])) {
            
            
     /*       $view         = $this->serviceManager->get('Zend\View\View');
            $jsonStrategy = $this->serviceManager->get('ViewJsonStrategy');

            // Attach strategy, which is a listener aggregate, at high priority
            $view->getEventManager()->attach($jsonStrategy, 100);
    */        
            
            $app = $this->serviceManager->get('application');
            $app->getEventManager()->attach('render', array($this, 'registerJsonStrategy'), 100);
            
   /*         $renderer = $this->serviceManager->get($this->outputRenderers[$format]);
            if (!$renderer instanceof OutputRendererInterface) {
                throw new OutputRenderer\Exception('Output renderer must implements AdminPanel\Service\OutputRendererInterface');
            }
            return $renderer->render($resultArray);
    * 
    * 
    */
        } else {
            throw new OutputRenderer\Exception('unknown format ' . $format);  
        }
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