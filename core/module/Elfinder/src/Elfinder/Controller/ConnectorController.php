<?php

namespace Elfinder\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ConnectorController extends AbstractActionController
{    
    public function indexAction()
    {
        if (!$this->isAllowed('file_system_access')) {	
            $this->response->setContent(json_encode(array('error' => 'У вас нет доступа к файловой системе')));
            return $this->response;
        }
        
        $connectorService = $this->serviceLocator->get('Elfinder\Service\Connector');
        $connectorService->run();
    }
}
