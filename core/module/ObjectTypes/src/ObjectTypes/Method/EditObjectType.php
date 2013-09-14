<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class EditObjectType extends AbstractMethod
{    
    public function main()
    {                
        $objectTypesCollection = $this->serviceLocator->get('App\Object\ObjectTypesCollection');
        $formElementManager = $this->serviceLocator->get('FormElementManager');
        
        $result = array();
        
        $result['tabs'] = $this->getTabs();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не передан id';
            return $result;
        }
        
        $objectTypeId = (int)$this->params()->fromRoute('id');        
        
        if (!$objectTypesCollection->getType($objectTypeId)) {
            $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не найден';
            return $result;
        }
                
        $objectTypeAdminEntity = $this->serviceLocator->get('ObjectTypes\Entity\ObjectTypeAdmin');
        $objectTypeAdminEntity->setObjectTypeId($objectTypeId);  
        
        $objectTypeForm = $objectTypeAdminEntity->getForm();
        
        $groupForm = $formElementManager->get('ObjectTypes\Form\FieldsGroupAdminForm');
        
        $fieldForm = $formElementManager->get('ObjectTypes\Form\FieldAdminForm');
        
        $fieldGroups = $objectTypeAdminEntity->getObjectTypeFieldGroups();
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ObjectTypes/form_modify.phtml',
            'data' => array(
                'objectTypeId'         => $objectTypeId,           
                'objectTypeForm'       => $objectTypeForm,
                'groupForm'            => $groupForm,
                'defaultGroupFormData' => $this->getDefaultGroupFormData(),
                'fieldForm'            => $fieldForm,
                'defaultFieldFormData' => $this->getDefaultFieldFormData(),
                'fieldGroups'          => $fieldGroups,
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
    
    protected function getDefaultGroupFormData()
    {
        $translator = $this->serviceLocator->get('translator');
        return array(
            'title' => $translator->translate('ObjectTypes:New group'),
        );
    }
    
    protected function getDefaultFieldFormData()
    {
        $fieldTypesCollection = $this->serviceLocator->get('fieldTypesCollection');
        $translator = $this->serviceLocator->get('translator');
        
        $defaultFieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('text');
        
        return array(
            'title' => $translator->translate('ObjectTypes:New field'),
            'field_type_id' => $defaultFieldTypeId,
        );
    }
    
    protected function getTabs()
    {
        $translator = $this->serviceLocator->get('translator');
        
        return array(
            array(
                'title' => $translator->translate('ObjectTypes:Object types'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'ObjectTypesList',                    
                )),     
                'active' => true,
            ),
            array(
                'title' => $translator->translate('ObjectTypes:Guides'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'GuidesList',
                )),
            ),
        );
    }
}

class EditObjectType2 extends AbstractMethod
{    
    public function main()
    {
        $objectTypesModel = new ObjectTypesModel($this->serviceLocator);
        $translator = $this->serviceLocator->get('translator');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        $result['tabs'] = array(
            array(
                'title' => $translator->translate('ObjectTypes:Object types'),
                'link' => $this->url()->fromRoute('admin/method', array(
                    'module' => 'ObjectTypes',
                    'method' => 'ObjectTypesList',                    
                )),     
                'active' => true,
            ),
            array(
                'title' => $translator->translate('ObjectTypes:Guides'),
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
                
        $tmp = $objectTypesModel->getObjectTypeForm($objectTypeId);
        
        $objectTypeFormConfig = $tmp['formConfig'];
        $objectTypeFormValues = $tmp['formValues'];
                
        $objectTypeFormMsg = array();
                
        $tmp = $objectTypesModel->getGroupForm();        
        $groupFormConfig = $tmp['formConfig'];
        $groupFormValues = $tmp['formValues'];
         
        
        $fieldGroups = $objectTypesModel->getObjectTypeFieldGroups($objectTypeId);
        
        $tmp = $objectTypesModel->getFieldForm();     
        $fieldFormConfig = $tmp['formConfig'];
        $fieldFormValues = $tmp['formValues'];
        
        if ($request->isPost()) {  
            $factory = new Factory($this->serviceLocator->get('FormElementManager'));
            
            $form = $factory->createForm($objectTypeFormConfig);  
            
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $values = $form->getData();
                
                $objectTypesModel->editObjectType($objectTypeId, $values);

                $this->flashMessenger()->addSuccessMessage('Тип данных успешно обновлен');
                return $this->redirect()->refresh();
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