<?php

namespace ObjectTypes\Model;

use Zend\Form\Factory;
use App\Field\Field;
use App\FieldsGroup\FieldsGroup;

class ObjectTypes
{

    protected $serviceManager;

    public function __construct($sm)
    {
        $this->serviceManager = $sm;

        $this->translator = $sm->get('translator');
        $this->db = $sm->get('db');
        $this->objectTypesCollection = $sm->get('objectTypesCollection');
        $this->fieldTypesCollection = $sm->get('fieldTypesCollection');
        $this->fieldsCollection = $sm->get('fieldsCollection');
    }
    
    public function editGroup($groupId, $data)
    {
        $result = array(
            'success' => false,
        );
        
        $fieldsGroup = new FieldsGroup(array(
            'serviceManager' => $this->serviceManager,
            'id' => $groupId,
        ));  
        
        if (!$fieldsGroup->isExists()) {
            $result['errMsg'] = 'Группа ' . $groupId . ' не найдена';
            return $result;
        }
        
        $tmp = $this->getGroupForm();
        $groupformConfig = $tmp['formConfig'];
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));
            
        $form = $factory->createForm($groupformConfig);      
        $form->setData($data);
        
        if ($form->isValid()) {
            $data = $form->getData();
            
            $objectTypeId = $fieldsGroup->getObjectTypeId();
            $objectType = $this->objectTypesCollection->getType($objectTypeId);
            
            $tmpGroup = $objectType->getFieldsGroupByName($data['name']);
            
            if (null !== $tmpGroup && $tmpGroup->getId() != $groupId) {
                $form->setMessages(array(
                    'name' => array(
                        'duplicate' => 'Группа с таким идентификатором уже существует',
                    ),
                ));
            } else {
                $fieldsGroup->setName($data['name'])->setTitle($data['title'])->save();
                $result['name'] = $data['name'];
                $result['title'] = $data['title'];
                $result['success'] = true;
            }            
        }
        $result['form'] = $form;
        
        return $result;
    }
    
    public function addGroup($objectTypeId, $data)
    {
        $result = array(
            'success' => false,
        );
        
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
            
        if (null === $objectType) {
            $result['errMsg'] = 'Не найден тип объекта ' . $objectTypeId;
            return $result;
        }
        
        $tmp = $this->getGroupForm();
        $groupformConfig = $tmp['formConfig'];
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));
            
        $form = $factory->createForm($groupformConfig);      
        $form->setData($data);
        
        if ($form->isValid()) {
            $data = $form->getData();
            
            if (null !== $objectType->getFieldsGroupByName($data['name'])) {
                $form->setMessages(array(
                    'name' => array(
                        'duplicate' => 'Группа с таким идентификатором уже существует',
                    ),
                ));
            } else {
                $result['groupId'] = $objectType->addFieldsGroup($data['name'], $data['title']);
                $result['name'] = $data['name'];
                $result['title'] = $data['title'];
                $result['success'] = true;
            }            
        }
        $result['form'] = $form;
        
        return $result;
    }
    
    public function editObjectType($objectTypeId, $values)
    {
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
            
        if (null === $objectType) {
            throw new \Exception('Тип данных ' . $objectTypeId . ' не найден');
        }
        
        $objectType->setName($values['name']);
        $objectType->setIsGuidable($values['is_guidable']);
        $objectType->setPageTypeId($values['page_type_id']);
        $objectType->setPageContentTypeId($values['page_content_type_id']);

        $objectType->save();
    }
    
    public function getObjectTypeForm($objectTypeId = null)
    {
        if (null === $objectTypeId) {
            $formValues = array();
        } else {
            $objectType = $this->objectTypesCollection->getType($objectTypeId);
            
            if (null === $objectType) {
                throw new \Exception('Тип данных ' . $objectTypeId . ' не найден');
            }
            
            $formValues = array(
                'name'                 => $objectType->getName(),
                'is_guidable'          => $objectType->getIsGuidable(),
                'page_type_id'         => $objectType->getPageTypeId(),
                'page_content_type_id' => $objectType->getPageContentTypeId(),
                'id'                   => $objectTypeId,
            );   
        }
        
        
        $sqlRes = $this->db->query('select id, title from ' . DB_PREF . 'page_types', array())->toArray();

        $pageTypes = array(
            '0' => '',
        );
        foreach ($sqlRes as $row) {
            $pageTypes[$row['id']] = $this->translator->translateI18n($row['title']);
        }

        $sqlRes = $this->db->query('select id, title from ' . DB_PREF . 'page_content_types', array())->toArray();

        $pageContentTypes = array(
            '0' => '',
        );
        foreach ($sqlRes as $row) {
            $pageContentTypes[$row['id']] = $this->translator->translateI18n($row['title']);
        }

        $formConfig = array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'name',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Object type name field'),
                        ),
                        'attributes' => array(
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Select',
                        'name' => 'page_type_id',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Page type field'),
                            'value_options' => $pageTypes,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Select',
                        'name' => 'page_content_type_id',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Page content type field'),
                            'value_options' => $pageContentTypes,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Checkbox',
                        'name' => 'is_guidable',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Is guidable field'),
                        ),
                    ),
                ),
            ),
            'fieldsets' => array(
                array(
                    'spec' => array(
                        'name' => 'submit',
                        'elements' => array(
                            array(
                                'spec' => array(
                                    'name' => 'submit',
                                    'attributes' => array(
                                        'type' => 'submit',
                                        'value' => $this->translator->translate('ObjectTypes:Save button'),
                                    ),
                                ),
                            ),
                        ),
                        'options' => array(
                            'label' => ' ',
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'name' => array(
                    'required' => true,
                ),
            ),
        );
        
        return array(
            'formConfig' => $formConfig,
            'formValues' => $formValues,
        );
    }
    
    public function editField($fieldId, $groupId, $data)
    {
        $result = array(
            'success' => false,
        );
        
        $field = new Field(array(
            'serviceManager' => $this->serviceManager,
            'id' => $fieldId,
        ));
        
        if (!$field->isExists()) {  
            $result['errMsg'] = 'Поле ' . $fieldId . ' не найдено';
            return $result;
        }
        
        $fieldsGroup = new FieldsGroup(array(
            'serviceManager' => $this->serviceManager,
            'id' => $groupId,
        ));
        if (!$fieldsGroup->isExists()) {  
            $result['errMsg'] = 'Группа ' . $groupId . ' не найдена';
            return $result;
        }
        
        
        $tmp = $this->getFieldForm();
        $fieldFormConfig = $tmp['formConfig'];
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));
            
        $form = $factory->createForm($fieldFormConfig);      
        $form->setData($data);

        if ($form->isValid()) {
            $data = $form->getData();
            
            $fieldsGroup->loadFields();
            $tmpField = $fieldsGroup->getFieldByName($data['name']);
            
            if (null !== $tmpField  && $tmpField->getId() != $fieldId) {
                $form->setMessages(array(
                    'name' => array(
                        'duplicate' => 'Поле с таким идентификатором уже существует',
                    ),
                ));
            } else {
                $field->setFieldData($data)->save();
                $result['name'] = $data['name'];
                $result['title'] = $data['title'];
                $result['fieldTypeName'] = $field->getFieldTypeName();

                $result['success'] = true;
            }
        }
        $result['form'] = $form;
        
        return $result;
    }
    
    public function addField($objectTypeId, $groupId, $data)
    {
        $result = array(
            'success' => false,
        );

        $tmp = $this->getFieldForm();
        $fieldFormConfig = $tmp['formConfig'];

        $factory = new Factory($this->serviceManager->get('FormElementManager'));

        $form = $factory->createForm($fieldFormConfig);      
        $form->setData($data);

        if ($form->isValid()) {
            $data = $form->getData();

            $objectType = $this->objectTypesCollection->getType($objectTypeId);
            $fieldsGroup = $objectType->getFieldsGroup($groupId);
            
            $tmpField = $fieldsGroup->getFieldByName($data['name']);
            if (null !== $tmpField) {
                $form->setMessages(array(
                    'name' => array(
                        'duplicate' => 'Поле с таким идентификатором уже существует',
                    ),
                ));
            } else {
                $fieldId = $this->fieldsCollection->addField($data);

                $field = new Field(array(
                    'serviceManager' => $this->serviceManager,
                    'id' => $fieldId,
                ));

                $result['id'] = $fieldId;
                $result['name'] = $data['name'];
                $result['title'] = $data['title'];
                $result['fieldTypeName'] = $field->getFieldTypeName();

                $groupName = $fieldsGroup->getName();

                $fieldsGroup->attachField($fieldId);

                $descendantTypeIds = $this->objectTypesCollection->getDescendantTypeIds($objectTypeId);
                foreach ($descendantTypeIds as $v) {
                    $tmpObjectType = $this->objectTypesCollection->getType($v);

                    $tmpFieldsGroup = $tmpObjectType->getFieldsGroupByName($groupName);

                    if (null !== $tmpFieldsGroup) {
                        $tmpFieldsGroup->attachField($fieldId);
                    }
                }            
                $result['success'] = true;
            }
        }        
        $result['form'] = $form;
        
        return $result;
    }
    
    public function getObjectTypeFieldGroups($objectTypeId)
    {
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
            
        if (null === $objectType) {
            throw new \Exception('Тип данных ' . $objectTypeId . ' не найден');
        }
        
        $tmpFieldGroups = $objectType->getFieldGroups();
        
        $fieldTypes = $this->fieldTypesCollection->getFieldTypes();
        foreach ($fieldTypes as $k=>$v) {
            $fieldTypes[$k] = $v->getName();
        }
            
        $fieldGroups = array();
        foreach ($tmpFieldGroups as $v) {
            $row = $v->getGroupData();
            $row['title'] = $this->translator->translateI18n($row['title']);
            
            $tmpFields = $v->getFields();

            $row['fields'] = array();
            foreach ($tmpFields as $v2) {
                $fieldData = $v2->getFieldData();
                $fieldData['title'] = $this->translator->translateI18n($fieldData['title']);
                $fieldData['field_type_name'] = $fieldTypes[$fieldData['field_type_id']];
                $row['fields'][] = $fieldData;
            }

            $fieldGroups[] = $row;
        }  
        
        return $fieldGroups;
    }
    
    

    public function getGroupForm()
    {
        $groupFormConfig = array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'title',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Group name field'),
                        ),
                        'attributes' => array(
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'name',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Group identifier field'),
                        ),
                        'attributes' => array(
                            'type' => 'text',
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'title' => array(
                    'required' => true,
                ),
                'name' => array(
                    'required' => true,
                ),
            ),
        );

        $groupFormValues = array(
            'title' => $this->translator->translate('ObjectTypes:New group'),
        );
        
        return array(
            'formConfig' => $groupFormConfig,
            'formValues' => $groupFormValues,
        );
    }

    public function getFieldForm()
    {
        $guides = $this->objectTypesCollection->getGuidesList();

        $fieldTypes = $this->fieldTypesCollection->getFieldTypes();
        foreach ($fieldTypes as $k => $v) {
            $fieldTypes[$k] = $v->getTitle();
        }


        $fieldFormConfig = array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'title',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field name field'),
                        ),
                        'attributes' => array(
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'name',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field identifier field'),
                        ),
                        'attributes' => array(
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'tip',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field tip field'),
                        ),
                        'attributes' => array(
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Select',
                        'name' => 'field_type_id',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field type field'),
                            'value_options' => $fieldTypes,
                        ),
                        'attributes' => array(
                            'type' => 'select',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Select',
                        'name' => 'guide_id',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field guide field'),
                            'empty_option' => '',
                            'value_options' => $guides,
                        ),
                        'attributes' => array(
                            'type' => 'select',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Checkbox',
                        'name' => 'is_required',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field is required field'),
                        ),
                        'attributes' => array(
                            'type' => 'checkbox',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Checkbox',
                        'name' => 'is_visible',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field is visible field'),
                        ),
                        'attributes' => array(
                            'type' => 'checkbox',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Checkbox',
                        'name' => 'in_filter',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field in filter field'),
                        ),
                        'attributes' => array(
                            'type' => 'checkbox',
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Zend\Form\Element\Checkbox',
                        'name' => 'in_search',
                        'options' => array(
                            'label' => $this->translator->translate('ObjectTypes:Field in search field'),
                        ),
                        'attributes' => array(
                            'type' => 'checkbox',
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'title' => array(
                    'required' => true,
                ),
                'name' => array(
                    'required' => true,
                ),
                'guide_id' => array(
                    'required' => false,
                ),
            ),
        );
        
        $defaultFieldTypeId = $this->fieldTypesCollection->getFieldTypeIdByDataType('text');
        
        $fieldFormValues = array(
            'title' => $this->translator->translate('ObjectTypes:New field'),
            'field_type_id' => $defaultFieldTypeId,
        );

        return array(
            'formConfig' => $fieldFormConfig,
            'formValues' => $fieldFormValues,
        );
    }

}