<?php

namespace Menu\Method;

use App\Method\AbstractMethod;
use Menu\Model\Menu;

class AddMenu extends AbstractMethod
{
    public function main()
    {
        $menuService = $this->serviceLocator->get('Menu\Service\Menu');
        $menuCollection = $this->serviceLocator->get('Menu\Collection\MenuCollection');
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();
        
        $parentObjectId = (int)$this->params()->fromRoute('id', 0); 
        if ($parentObjectId) {
            if (!$menuService->isObjectRubric($parentObjectId)) {
                $result['errMsg'] = 'Объект ' . $parentObjectId . ' не является меню';
                return $result;
            }
        }     
        
        $menuCollection->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = $objectTypesCollection->getTypeIdByGuid($menuService->getRubricGuid());  
            $menuCollection->setObjectTypeId($objectTypeId);
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $menuCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $menuService->getRubricTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является рубрикой';
                return $result;
            }
        }       
        
        if ($request->isPost()) {
            $form = $menuCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('Pages:(Page without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                $form->getData();
                exit('ok');
                
                if ($menuId = $menuCollection->addMenu($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Меню создано');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Menu',
                            'method' => 'Edit',
                            'id' => $menuId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Меню создано',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При создании меню произошли ошибки';
                }
            } else {
                exit('*');
                $result['success'] = false;
            }
        } else {
            $form = $menuCollection->getForm(true);
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
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddMenu', $params),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
    
}