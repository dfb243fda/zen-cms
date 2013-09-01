<?php

namespace DirectAccessToMethods\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DirectAccessToMethods\Exception\DirectAccessException;

class DirectController extends AbstractActionController
{    
    public function indexAction()
    {
        $directAccessService = $this->serviceLocator->get('DirectAccessToMethods\Service\DirectAccess');
        $systemInfoService = $this->serviceLocator->get('DirectAccessToMethods\Service\SystemInfo');
        $errorsService = $this->serviceLocator->get('DirectAccessToMethods\Service\Errors');
        $rendererStrategy = $this->serviceLocator->get('App\View\RendererStrategy');       
        $rendererStrategyOptions = $this->serviceLocator->get('DirectAccessToMethods\View\RendererStrategyOptions');    
        
        $module = (string)$this->params()->fromRoute('module');
        $method = (string)$this->params()->fromRoute('method');
        
        $args = array();
        for ($i = 1; $i <= 5; $i++) {
            if (null === $this->params()->fromRoute('param' . $i)) {
                break;
            }
            $args[] = $this->params()->fromRoute('param' . $i);
        }
        
        $directAccessService->setModule($module)->setMethod($method)->setArgs($args);
        
        $resultArray = array();
        
        try {
            $resultArray['result'] = $directAccessService->getMethodResult();
        } catch (DirectAccessException $e) {
            $resultArray['errMsg'] = 'В доступе отказано';
        }
        
        $resultArray['systemInfo'] = $systemInfoService->getSystemInfo();
        
        $errors = $errorsService->getErrors();
        if (!empty($errors)) {
            $resultArray['errors'] = $errors;
        }
        
        $rendererStrategy->setFormat($rendererStrategyOptions->getFormat());
        
        $rendererStrategy->registerStrategy();        
                        
        return $rendererStrategy->getResult($resultArray);
    }
}
