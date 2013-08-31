<?php

namespace DirectAccessToMethods\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

abstract class OutputRendererAbstract implements 
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
    
    
    protected function removeObjectsFromArray(array &$theArray) {
		foreach ($theArray as &$value) {
			if (is_array($value)) {
				$this->removeObjectsFromArray($value);
			} elseif (is_object($value)) {
				$value = '[Object ' . get_class($value) . ']';;
			}
		}
		unset($value);
		reset($theArray);
	}
}