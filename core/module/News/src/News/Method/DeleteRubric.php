<?php

namespace News\Method;

use App\Method\AbstractMethod;

class DeleteRubric extends AbstractMethod
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
          
        if ($newsService->isObjectRubric($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Рубрика удалена';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить рубрику';
                $result['success'] = false;
            }
        } else {
            $result = array(
                'errMsg' => 'Рубрика ' . $objectId . ' не найдена',
            );
        }
        
        return $result;
    }
}