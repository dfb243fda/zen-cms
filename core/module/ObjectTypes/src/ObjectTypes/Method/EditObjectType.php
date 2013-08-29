<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use Zend\Form\Factory;
use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class EditObjectType extends AbstractMethod
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