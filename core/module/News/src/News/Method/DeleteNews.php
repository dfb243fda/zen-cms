<?php

namespace News\Method;

use App\Method\AbstractMethod;

class DeleteNews extends AbstractMethod
{
    public function main()
    {        
        $newsService = $this->serviceLocator->get('News\Service\News');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        
        $result = array();
        
        if (null === $this->params()->fromPost('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';            
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromPost('id');
          
        if ($newsService->isObjectNews($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Новость удалена';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить новость';
                $result['success'] = false;
            }
        } else {
            $result['errMsg'] = 'Новость ' . $objectId . ' не найдена';
        }
        
        return $result;
    }
}