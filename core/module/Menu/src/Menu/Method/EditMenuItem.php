<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

class EditMenuItem extends AbstractMethod
{
    public function main()
    {        
        $menuItemEntity = $this->serviceLocator->get('Menu\Entity\MenuItemEntity');
        $menuService = $this->serviceLocator->get('Menu\Service\Menu');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';            
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if (!$menuService->isObjectItem($objectId)) {
            $result['errMsg'] = 'Пункт меню ' . $objectId . ' не найден';
            return $result;
        }
                
        $menuItemEntity->setObjectId($objectId);
                
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            if (!in_array($objectTypeId, $menuService->getItemTypeIds())) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
            $menuItemEntity->setObjectTypeId($objectTypeId);
        }
        
        if ($request->isPost()) {
            $form = $menuItemEntity->getForm(false);
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                if ($menuItemEntity->editMenuItem($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Пункт меню успешно обновлен');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Menu',
                            'method' => 'EditMenuItem',
                            'id' => $objectId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Пункт меню успешно обновлен',
                    );  
                } else {
                    return array(
                        'success' => false,
                        'msg' => 'При обновлении пункта меню произошли ошибки',
                    );  
                }                       
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $menuItemEntity->getForm(true);    
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Menu/menu_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditMenuItem', array(
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