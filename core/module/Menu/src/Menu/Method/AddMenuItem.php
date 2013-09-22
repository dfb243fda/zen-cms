<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

class AddMenuItem extends AbstractMethod
{
    public function main()
    {
        $menuService = $this->serviceLocator->get('Menu\Service\Menu');
        $menuItemsCollection = $this->serviceLocator->get('Menu\Collection\MenuItemsCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        $parentObjectId = (int)$this->params()->fromRoute('id', 0); 
        if ($parentObjectId) {
            if (!$menuService->isObjectMenu($parentObjectId)) {
                $result['errMsg'] = 'Объект ' . $parentObjectId . ' не является меню';
                return $result;
            }
        }     
        
        $menuItemsCollection->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = $objectTypesCollection->getTypeIdByGuid($menuService->getItemGuid());  
            $menuItemsCollection->setObjectTypeId($objectTypeId);
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $menuItemsCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $menuService->getItemTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является пунктом меню';
                return $result;
            }
        }       
        
        if ($request->isPost()) {
            $form = $menuItemsCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('Pages:(Page without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($menuItemId = $menuItemsCollection->addMenuItem($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Пункт меню создан');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Menu',
                            'method' => 'EditMenuItem',
                            'id' => $menuItemId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Пункт меню создан',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При создании меню произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $menuItemsCollection->getForm(true);
        }
        
        $params = array(
            'objectTypeId' => '--OBJECT_TYPE--',    
        );
        if (0 != $parentObjectId) {
            $params['id'] = $parentObjectId;
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Menu/menu_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddMenuItem', $params),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
}