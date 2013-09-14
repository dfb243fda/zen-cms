<?php

namespace ObjectTypes\Model;

use Zend\Form\Factory;

class Guides
{
    protected $objectsTable = 'objects';
    
    protected $serviceManager;
    
    protected $db;
    
    protected $guideItemId;
    
    protected $guideId;
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        $this->db = $sm->get('db');
        $this->objectsCollection = $sm->get('objectsCollection');
        $this->objectTypesCollection = $sm->get('objectTypesCollection');
        $this->objectPropertyCollection = $sm->get('objectPropertyCollection');
    }
    
    public function isGuidable($objectTypeId)
    {
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $objectType = $objectTypesCollection->getType($objectTypeId);
        
        if ($objectType->getIsGuidable()) {
            return true;
        }
        return false;
    }
    
    public function getGuideItems($guideId)
    {
        $sqlRes = $this->db->query('select * from ' . DB_PREF . $this->objectsTable . ' where type_id = ? and is_deleted = 0', array($guideId))->toArray();
        
        $result = array();        
        foreach ($sqlRes as $row) {
            $result[$row['id']] = $row;
        }
        
        return $result;
    }
    
    public function setGuideItemId($guideItemId)
    {
        $this->guideItemId = $guideItemId;
        return $this;
    }
    
    public function setGuideId($guideId)
    {
        $this->guideId = $guideId;
        return $this;
    }
    
    public function getGuideItemForm()
    {                
        $baseForm = $this->getBaseFormConfig();
        
        if (null === $this->guideItemId) {      
            $guideId = $this->guideId;
            
            $data = array();
            
            $objectType = $this->objectTypesCollection->getType($guideId);
            $formConfig = $objectType->getAppFormConfig($baseForm);
            
            $formValues = array();                        
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {                    
                    if (isset($data[$k2])) {
                        $formValues[$k][$k2] = $data[$k2];
                    }          
                }
            }
        } else {
            $guideItemId = $this->guideItemId;
            
            $object = $this->objectsCollection->getObject($guideItemId);
            
            $guideId = $object->getTypeId();
            
            $data = array(
                'name' => $object->getName(),
            );
            
            $objectType = $this->objectTypesCollection->getType($guideId);
            $formConfig = $objectType->getAppFormConfig($baseForm);
            
            $formValues = array();                        
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {
                    if ('field_' == substr($k2, 0, 6)) {
                        $fieldId = substr($k2, 6);
                        $property = $this->objectPropertyCollection->getProperty($guideItemId, $fieldId); 
                        $formValues[$k][$k2] = $property->getValue();
                    } else {
                        if (isset($data[$k2])) {
                            $formValues[$k][$k2] = $data[$k2];
                        }
                    }                    
                }
            }
        }
             
        return array(
            'formConfig' => $formConfig,
            'formValues' => $formValues,
        );
    }
    
    protected function getBaseFormConfig()
    {        
        return array(
            'input_filter' => array(
                'common' => array(
                    'type' => 'Zend\InputFilter\InputFilter',
                    'name' => array(
                        'required' => true,
                    ),
                ),
            ),
        );
    }
    
    public function addGuideItem($data)
    {
        $data = (array)$data;
        
        $objectTypeId = $this->guideId;
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
        
        $tmp = $this->getGuideItemForm();
        $formConfig = $tmp['formConfig'];
        
        $result = array();
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);     
        
        $form->setData($data);
        
        if ($form->isValid()) { 
            $data = $form->getData();
                
            $insertFields = array();
            $insertBase = array();

            foreach ($data as $groupKey=>$groupData) {
                foreach ($groupData as $fieldName=>$fieldVal) {
                    if ('field_' == substr($fieldName, 0, 6)) {
                        $insertFields[substr($fieldName, 6)] = $fieldVal;
                    } else {
                        $insertBase[$fieldName] = $fieldVal;
                    }
                }
            }
        
            $sqlRes = $this->db->query('select max(sorting) as max_sorting from ' . DB_PREF . 'objects WHERE type_id = ?', array($objectTypeId))->toArray();
            
            if (empty($sqlRes)) {
                $sorting = 0;
            } else {
                $sorting = $sqlRes[0]['max_sorting'] + 1;
            }
            
            $objectId = $this->objectsCollection->addObject($insertBase['name'], $objectTypeId, 0, $sorting);

            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $fields = $v->getFields();

                foreach ($fields as $k2=>$v2) {                    
                    if (array_key_exists($k2, $insertFields)) {
                        $property = $this->objectPropertyCollection->getProperty($objectId, $k2); 
                        $property->setValue($insertFields[$k2])->save();
                    }
                }
            }
            
            $result['guideItemId'] = $objectId;
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
    }
    
    public function editGuideItem($data)
    {
        $data = (array)$data;
        
        $guideItemId = $this->guideItemId;
        $object = $this->objectsCollection->getObject($guideItemId);  
        
        $objectTypeId = $object->getTypeId();
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
        
        $result = array();
        
        
        $tmp = $this->getGuideItemForm();
        $formConfig = $tmp['formConfig'];
               
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);

        $form->setData($data);
        
        if ($form->isValid()) {
            $data = $form->getData();
                
            $insertFields = array();
            $insertBase = array();

            foreach ($data as $groupKey=>$groupData) {
                foreach ($groupData as $fieldName=>$fieldVal) {
                    if ('field_' == substr($fieldName, 0, 6)) {
                        $insertFields[substr($fieldName, 6)] = $fieldVal;
                    } else {
                        $insertBase[$fieldName] = $fieldVal;
                    }
                }
            }
        
            $object->setName($insertBase['name'])->save();

            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $fields = $v->getFields();

                foreach ($fields as $k2=>$v2) {                    
                    if (array_key_exists($k2, $insertFields)) {
                        $property = $this->objectPropertyCollection->getProperty($guideItemId, $k2); 
                        $property->setValue($insertFields[$k2])->save();
                    }
                }
            }
            
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result; 
    }
    
    public function deleteGuideItem($guideItemId)
    {
        return $this->objectsCollection->delObject($guideItemId);
    }
}