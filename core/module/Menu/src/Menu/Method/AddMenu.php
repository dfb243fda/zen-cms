<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

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
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = $objectTypesCollection->getTypeIdByGuid($menuService->getMenuGuid());  
            $menuCollection->setObjectTypeId($objectTypeId);
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $menuCollection->setObjectTypeId($objectTypeId);
            
            if (!in_array($objectTypeId, $menuService->getMenuTypeIds())) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не является меню';
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
                $result['success'] = false;
            }
        } else {
            $form = $menuCollection->getForm(true);
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Menu/menu_form.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddMenu', array(
                        'objectTypeId' => '--OBJECT_TYPE--',    
                    )),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
    
}