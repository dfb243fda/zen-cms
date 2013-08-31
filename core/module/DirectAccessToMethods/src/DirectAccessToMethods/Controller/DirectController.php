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
        $outputService = $this->serviceLocator->get('DirectAccessToMethods\Service\OutputRenderer'); 
        
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
        $resultArray['errors'] = empty($errors) ? null : $errors;
        
        $output = $outputService->getOutput($resultArray);
        
        $this->response->setContent($result); 
        return $this->response;   
    }
}
