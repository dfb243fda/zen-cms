<?php

namespace App\View\ResultComposer;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

abstract class ComposerAbstract implements 
    ServiceManagerAwareInterface,
    ComposerInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $target;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
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