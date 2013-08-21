<?php

namespace Menu\Method;

use App\Method\AbstractMethod;
use Menu\Model\Menu;

class Edit extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $db;
    
    protected $menuModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->menuModel = new Menu($this->rootServiceLocator);        
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromRoute('id');
  
        if ($this->menuModel->isObjectRubric($objectId)) {
            $menuType = Menu::RUBRIC;
        } elseif ($this->menuModel->isObjectItem($objectId)) {
            $menuType = Menu::ITEM;
        } else {
            $result['errMsg'] = 'Объект ' . $objectId . ' не является ни меню, ни пунктом меню';
            return $result;
        }
        
        $this->menuModel->setMenuType($menuType)->setObjectId($objectId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
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
            'name' => 'content_template/Menu/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/EditMenu', array(
                        'id' => $objectId,
                        'objectTypeId' => '--OBJECT_TYPE--',            
                    )),
                ),
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg' => $formMessages,
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