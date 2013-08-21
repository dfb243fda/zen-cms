<?php

namespace Pages\Method;

use App\Method\AbstractMethod;
use Pages\Model\Domains;

class DeleteDomain extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $domainsModel;
    
    protected $request;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->domainsModel = new Domains($this->rootServiceLocator);        
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array();
        
        $domainId = $this->request->getPost('id');
        if (null === $domainId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $domainId = (int)$domainId;
        
        if ($this->domainsModel->delete($domainId)) {
            $result['success'] = true;
            $result['msg'] = 'Домен успешно удален';
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'Не удалось удалить домен';
        }
        
        return $result;      
    }
}