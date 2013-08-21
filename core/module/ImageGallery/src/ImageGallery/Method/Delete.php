<?php

namespace ImageGallery\Method;

use App\Method\AbstractMethod;
use ImageGallery\Model\ImageGallery;

class Delete extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->objectsCollection = $this->rootServiceLocator->get('objectsCollection');
        $this->imageGalleryModel = new ImageGallery($this->rootServiceLocator);        
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array(
            'success' => false,
        );
        
        if (null === $this->request->getPost('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        
        $objectId = (int)$this->request->getPost('id');
        
        if ($this->imageGalleryModel->isObjectRubric($objectId)) {
            $galleryType = ImageGallery::RUBRIC;
        } elseif ($this->imageGalleryModel->isObjectItem($objectId)) {
            $galleryType = ImageGallery::ITEM;
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни галереей, ни изображением';
            return $result;
        }
        
        if ($this->imageGalleryModel->del($objectId)) {
            $result['success'] = true;
            if (ImageGallery::RUBRIC == $galleryType) {
                $result['msg'] = 'Галерея удалена';
            } else {
                $result['msg'] = 'Изображение удалено';
            }   
        } else {
            if (ImageGallery::RUBRIC == $galleryType) {
                $result['errMsg'] = 'Не удалось удалить галерею';
            } else {
                $result['errMsg'] = 'Не удалось удалить изображение';
            }            
        }
        
        return $result;
    }
}