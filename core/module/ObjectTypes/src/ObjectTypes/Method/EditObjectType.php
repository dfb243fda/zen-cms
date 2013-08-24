<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use Zend\Form\Factory;
use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class EditObjectType extends AbstractMethod
{    
    protected $objectTypesModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->fieldTypesCollection = $this->rootServiceLocator->get('fieldTypesCollection');
        $this->objectTypesModel = new ObjectTypesModel($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }


    public function main()
    {
        $result = array();
        
        $result['tabs'] = array(
            array(
                'title' => $this->translator->translate('ObjectTypes:Object types'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'ObjectTypesList',                    
                )),     
                'active' => true,
            ),
            array(
                'title' => $this->translator->translate('ObjectTypes:Guides'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'GuidesList',
                )),
            ),
        );
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не передан id';
            return $result;
        }
        
        $objectTypeId = (int)$this->params()->fromRoute('id');
                
        $tmp = $this->objectTypesModel->getObjectTypeForm($objectTypeId);
        
        $objectTypeFormConfig = $tmp['formConfig'];
        $objectTypeFormValues = $tmp['formValues'];
                
        $objectTypeFormMsg = array();
                
        $tmp = $this->objectTypesModel->getGroupForm();        
        $groupFormConfig = $tmp['formConfig'];
        $groupFormValues = $tmp['formValues'];
         
        
        $fieldGroups = $this->objectTypesModel->getObjectTypeFieldGroups($objectTypeId);
        
        $tmp = $this->objectTypesModel->getFieldForm();     
        $fieldFormConfig = $tmp['formConfig'];
        $fieldFormValues = $tmp['formValues'];
        
        if ($this->request->isPost()) {  
            $factory = new Factory($this->rootServiceLocator->get('FormElementManager'));
            
            $form = $factory->createForm($objectTypeFormConfig);  
            
            $form->setData($this->request->getPost());
            if ($form->isValid()) {
                $values = $form->getData();
                
                $this->objectTypesModel->editObjectType($objectTypeId, $values);

                $this->flashMessenger()->addSuccessMessage('Тип данных успешно обновлен');
                $this->redirect()->refresh();

                $result['success'] = true;
                $result['msg'] = 'Тип данных успешно обновлен';
                return $result;
            } else {
                $objectTypeFormMsg = $form->getMessages();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ObjectTypes/form_modify.phtml',
            'data' => array(
                'objectTypeId'         => $objectTypeId,
                'objectTypeFormConfig' => $objectTypeFormConfig,
                'objectTypeFormValues' => $objectTypeFormValues,                
                'objectTypeFormMsg'    => $objectTypeFormMsg,
                'groupFormConfig'      => $groupFormConfig,
                'groupFormValues'      => $groupFormValues,
                'fieldGroups'          => $fieldGroups,
                'fieldFormConfig'      => $fieldFormConfig,
                'fieldFormValues'      => $fieldFormValues,
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