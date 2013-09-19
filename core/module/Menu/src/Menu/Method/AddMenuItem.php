<?php

namespace Menu\Method;

use App\Method\AbstractMethod;
use Menu\Model\Menu;

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
                $form->getData();
                exit('ok');
                
                if ($menuId = $menuItemsCollection->addMenuItem($form->getData())) {
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
            $form = $menuItemsCollection->getForm(true);
        }
        
        $params = array(
            'objectTypeId' => '--OBJECT_TYPE--',    
        );
        if (0 != $parentObjectId) {
            $params['id'] = $parentObjectId;
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Menu/menu_form.phtml3',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddMenu', $params),
                ),
                'form' => $form,
            ),
        );
        
        return $result;
    }
    
    public function init2()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->objectsCollection = $this->rootServiceLocator->get('objectsCollection');
        $this->menuModel = new Menu($this->rootServiceLocator);        
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main2()
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