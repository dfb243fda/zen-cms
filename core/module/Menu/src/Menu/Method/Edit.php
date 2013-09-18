<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

class Edit extends AbstractMethod
{
    public function main()
    {        
        $menuService = $this->serviceLocator->get('Menu\Service\Menu');
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if ($menuService->isObjectRubric($objectId)) {
            $result = $this->editRubric($objectId);
        } elseif ($menuService->isObjectItem($objectId)) {
            $result = $this->editItem($objectId);
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни меню, ни пунктом меню';
            return $result;
        }
    }
    
    public function editRubric($objectId)
    {
        $menuRubric = $this->serviceLocator->get('Menu\Entity\MenuRubric');
        $menuService = $this->serviceLocator->get('Menu\Service\Menu');
        
        $menuRubric->setObjectId($objectId);
        
        $result = array();
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $menuRubric->setObjectTypeId($objectTypeId);
            
            if (!$menuService->isObjectTypeCorrect($objectTypeId)) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
        }
        
        $form = $menuRubric->getForm();        
        
        if ($this->request->isPost()) {
            $tmp = $this->menuModel->edit($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Меню успешно обновлено');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Menu',
                        'method' => 'Edit',
                        'id' => $objectId,
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Меню успешно обновлено',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
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