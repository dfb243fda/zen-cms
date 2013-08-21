<?php

namespace Menu\Method;

use App\Method\AbstractMethod;
use Menu\Model\Menu;

class AddMenuItem extends AbstractMethod
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
        $result = array();
                
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не передано меню для добавления';
            return $result;
        }
        
        $parentObjectId = (int)$this->params()->fromRoute('id'); 
        if (!$this->menuModel->isObjectRubric($parentObjectId)) {
            $result['errMsg'] = 'Тип объекта ' . $parentObjectId . ' не является меню';
            return $result;
        }
        
        $this->menuModel->setMenuType(Menu::ITEM)->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectType = $this->objectTypesCollection->getType($this->menuModel->getItemGuid());        
            $objectTypeId = $objectType->getId();
            $this->menuModel->setObjectTypeId($objectTypeId);
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $this->menuModel->setObjectTypeId($objectTypeId);
            
            if (!$this->menuModel->isObjectTypeCorrect($objectTypeId)) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
        }       
        
        $form = $this->menuModel->getForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->menuModel->add($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Пункт меню успешно добавлен');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Menu',
                        'method' => 'Edit',
                        'id' => $tmp['objectId'],
                    ));
                }

                return array(
                    'success' => 1,
                    'msg' => 'Пункт меню успешно добавлен',
                );         
            } else {
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Menu/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddMenuItem', array(
                        'id' => $parentObjectId,
                        'objectTypeId' => '--OBJECT_TYPE--',            
                    )),
                ),
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg' => $formMessages,
            ),
        );
        
        return $result;
    }
}