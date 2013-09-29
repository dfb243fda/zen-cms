<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;

class DeleteGallery extends AbstractMethod
{
    public function main()
    {        
        $galService = $this->serviceLocator->get('ImageGallery\Service\ImageGallery');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        
        $result = array();
        
        if (null === $this->params()->fromPost('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';            
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromPost('id');
          
        if ($galService->isObjectGallery($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Галерея удалена';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить галерею';
                $result['success'] = false;
            }
        } else {
            $result = array(
                'errMsg' => 'Галерея ' . $objectId . ' не найдена',
            );
        }
        
        return $result;
    }
}