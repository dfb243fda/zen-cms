<?php

namespace Menu\Method;

use App\Method\AbstractMethod;
use Menu\Model\Menu;

class Delete extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->objectsCollection = $this->rootServiceLocator->get('objectsCollection');
        $this->menuModel = new Menu($this->rootServiceLocator);        
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
        
        if ($this->menuModel->isObjectRubric($objectId)) {
            $menuType = Menu::RUBRIC;
        } elseif ($this->menuModel->isObjectItem($objectId)) {
            $menuType = Menu::ITEM;
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни меню, ни пунктом меню';
            return $result;
        }
        
        if ($this->menuModel->del($objectId)) {
            $result['success'] = true;
            if (Menu::RUBRIC == $menuType) {
                $result['msg'] = 'Меню удалено';
            } else {
                $result['msg'] = 'Пункт меню удален';
            }   
        } else {
            if (Menu::RUBRIC == $menuType) {
                $result['errMsg'] = 'Не удалось удалить меню';
            } else {
                $result['errMsg'] = 'Не удалось удалить пункт меню';
            }            
        }
        
        return $result;
    }
}