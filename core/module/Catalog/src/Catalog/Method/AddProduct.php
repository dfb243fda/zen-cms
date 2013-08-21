<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;
use Catalog\Model\Catalog;

class AddProduct extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->objectsCollection = $this->rootServiceLocator->get('objectsCollection');
        $this->catalogModel = new Catalog($this->rootServiceLocator);        
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array();
                
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не передана рубрика для добавления';
            return $result;
        }
        
        $parentObjectId = (int)$this->params()->fromRoute('id'); 
        if (!$this->catalogModel->isObjectRubric($parentObjectId)) {
            $result['errMsg'] = 'Тип объекта ' . $parentObjectId . ' не является рубрикой новостей';
            return $result;
        }
        
        $this->catalogModel->setCatalogType(Catalog::ITEM)->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectType = $this->objectTypesCollection->getType($this->catalogModel->getItemGuid());        
            $objectTypeId = $objectType->getId();
            $this->catalogModel->setObjectTypeId($objectTypeId);
        } else {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $this->catalogModel->setObjectTypeId($objectTypeId);
            
            if (!$this->catalogModel->isObjectTypeCorrect($objectTypeId)) {
                $result['errMsg'] = 'Передан неверный тип объекта ' . $objectTypeId;
                return $result;
            }
        }       
        
        $form = $this->catalogModel->getForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->catalogModel->add($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Продукт успешно добавлен');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Catalog',
                        'method' => 'Edit',
                        'id' => $tmp['objectId'],
                    ));
                }

                return array(
                    'success' => 1,
                    'msg' => 'Продукт успешно добавлен',
                );         
            } else {
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Catalog/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddProduct', array(
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