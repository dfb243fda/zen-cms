<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

class EditMenu extends AbstractMethod
{
    public function main()
    {        
        $menuEntity = $this->serviceLocator->get('Menu\Entity\MenuEntity');
        $menuService = $this->serviceLocator->get('Menu\Service\Menu');
        $request = $this->serviceLocator->get('request');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if (!$menuService->isObjectMenu($objectId)) {
            $result['errMsg'] = 'Меню ' . $objectId . ' не найдено';
            return $result;
        } 
                
        $object = $objectsCollection->getObject($objectId);
        
        $menuEntity->setObjectId($objectId);
                
        if (null === $this->params()->fromRoute('objectTypeId')) {            
            $objectTypeId = $object->getTypeId();
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $menuService->getMenuTypeIds())) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
        }
        $menuEntity->setObjectTypeId($objectTypeId);
            
        
        if ($request->isPost()) {
            $form = $menuEntity->getForm(false);
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                if ($menuEntity->editMenu($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Меню успешно обновлено');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Menu',
                            'method' => 'EditMenu',
                            'id' => $objectId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Меню успешно обновлено',
                    );  
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При обновлении меню произошли ошибки',
                    );  
                }                       
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $menuEntity->getForm(true);    
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Menu/menu_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditMenu', array(
                        'id' => $objectId,
                        'objectTypeId' => '--OBJECT_TYPE--',            
                    )),
                ),
                'form' => $form,
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
        return $result;  
    }
}