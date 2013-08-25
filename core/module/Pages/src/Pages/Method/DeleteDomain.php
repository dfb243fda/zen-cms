<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class DeleteDomain extends AbstractMethod
{
    public function main()
    {
        $domainsCollection = $this->serviceLocator->get('Pages\Collection\Domains');
        
        $result = array();
        
        $domainId = $this->params()->fromPost('id');
        if (null === $domainId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $domainId = (int)$domainId;
        
        if ($domainsCollection->deleteDomain($domainId)) {
            $result['success'] = true;
            $result['msg'] = 'Домен успешно удален';
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'Не удалось удалить домен';
        }
        
        return $result; 
    }
}