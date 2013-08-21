<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;
use Catalog\Model\Catalog;

class AddCatalog extends AbstractMethod
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
        
        $parentObjectId = (int)$this->params()->fromRoute('id', 0); 
        if ($parentObjectId) {
            if (!$this->catalogModel->isObjectRubric($parentObjectId)) {
                $result['errMsg'] = 'Объект ' . $parentObjectId . ' не является каталогом';
                return $result;
            }
        }        
        
        $this->catalogModel->setCatalogType(Catalog::RUBRIC)->setParentObjectId($parentObjectId);
        
        if (null === $this->params()->fromRoute('objectTypeId')) {
            $objectType = $this->objectTypesCollection->getType($this->catalogModel->getRubricGuid());        
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
                    $this->flashMessenger()->addSuccessMessage('Каталог успешно создан');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Catalog',
                        'method' => 'Edit',
                        'id' => $tmp['objectId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Каталог успешно создан',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $changeObjectTypeUrlParams = array(
            'objectTypeId' => '--OBJECT_TYPE--',            
        );
        if (0 != $parentObjectId) {
            $changeObjectTypeUrlParams['id'] = $parentObjectId;
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Catalog/form_view.phtml',
            'data' => array(
                'jsArgs' => array(
                    'changeObjectTypeUrlTemplate' => $this->url()->fromRoute('admin/AddCatalog', $changeObjectTypeUrlParams),
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