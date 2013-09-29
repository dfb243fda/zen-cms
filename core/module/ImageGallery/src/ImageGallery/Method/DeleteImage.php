<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;

class DeleteImage extends AbstractMethod
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
          
        if ($galService->isObjectImage($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Изображение удалено';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить изображение';
                $result['success'] = false;
            }
        } else {
            $result = array(
                'errMsg' => 'Изображение ' . $objectId . ' не найдено',
            );
        }
        
        return $result;
    }
}