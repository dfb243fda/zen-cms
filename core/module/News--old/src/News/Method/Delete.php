<?php

namespace News\Method;

use App\Method\AbstractMethod;
use News\Model\News;

class Delete extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->objectsCollection = $this->rootServiceLocator->get('objectsCollection');
        $this->newsModel = new News($this->rootServiceLocator);        
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
        
        if ($this->newsModel->isObjectRubric($objectId)) {
            $newsType = News::RUBRIC;
        } elseif ($this->newsModel->isObjectItem($objectId)) {
            $newsType = News::ITEM;
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни рубрикой, ни новостью';
            return $result;
        }
        
        if ($this->newsModel->del($objectId)) {
            $result['success'] = true;
            if (News::RUBRIC == $newsType) {
                $result['msg'] = 'Рубрика удалена';
            } else {
                $result['msg'] = 'Новость удалена';
            }   
        } else {
            if (News::RUBRIC == $newsType) {
                $result['errMsg'] = 'Не удалось удалить рубрику';
            } else {
                $result['errMsg'] = 'Не удалось удалить новость';
            }            
        }
        
        return $result;
    }
}