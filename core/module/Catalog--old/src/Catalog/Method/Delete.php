<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;
use Catalog\Model\Catalog;

class Delete extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->objectsCollection = $this->rootServiceLocator->get('objectsCollection');
        $this->catalogModel = new Catalog($this->rootServiceLocator);        
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
        
        if ($this->catalogModel->isObjectRubric($objectId)) {
            $catalogType = Catalog::RUBRIC;
        } elseif ($this->catalogModel->isObjectItem($objectId)) {
            $catalogType = Catalog::ITEM;
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни каталогом, ни товаром';
            return $result;
        }
        
        if ($this->catalogModel->del($objectId)) {
            $result['success'] = true;
            if (Catalog::RUBRIC == $catalogType) {
                $result['msg'] = 'Каталог удален';
            } else {
                $result['msg'] = 'Товар удален';
            }   
        } else {
            if (Catalog::RUBRIC == $catalogType) {
                $result['errMsg'] = 'Не удалось удалить каталог';
            } else {
                $result['errMsg'] = 'Не удалось удалить товар';
            }            
        }
        
        return $result;
    }
}