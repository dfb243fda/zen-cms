<?php

namespace ObjectTypes\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ObjectTypeAdmin implements ServiceManagerAwareInterface
{
    protected $objectTypeId;
    
    protected $serviceManager;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function getForm()
    {        
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        if (null === $this->objectTypeId) {
            $formData = array();
        } else {
            $objectType = $objectTypesCollection->getType($this->objectTypeId);
            
            if (null === $objectType) {
                throw new \Exception('Тип данных ' . $this->objectTypeId . ' не найден');
            }
            
            $formData = array(
                'name'                 => $objectType->getName(),
                'is_guidable'          => $objectType->getIsGuidable(),
                'page_type_id'         => $objectType->getPageTypeId(),
                'page_content_type_id' => $objectType->getPageContentTypeId(),
                'id'                   => $this->objectTypeId,
            );   
        }
        
        
        $formElementManager = $this->serviceManager->get('formElementManager');
        $form = $formElementManager->get('ObjectTypes\Form\ObjectTypeAdminForm');
        
        $form->setData($formData);
        
        return $form;        
    }
    
    public function getObjectTypeFieldGroups()
    {
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');
        $translator = $this->serviceManager->get('translator');
        
        $objectTypeId = $this->objectTypeId;
        
        $objectType = $objectTypesCollection->getType($objectTypeId);
            
        if (null === $objectType) {
            throw new \Exception('Тип данных ' . $objectTypeId . ' не найден');
        }
        
        $tmpFieldGroups = $objectType->getFieldGroups();
        
        $fieldTypes = $fieldTypesCollection->getFieldTypes();
        foreach ($fieldTypes as $k=>$v) {
            $fieldTypes[$k] = $v->getName();
        }
            
        $fieldGroups = array();
        foreach ($tmpFieldGroups as $v) {
            $row = $v->getGroupData();
            $row['title'] = $translator->translateI18n($row['title']);
            
            $tmpFields = $v->getFields();

            $row['fields'] = array();
            foreach ($tmpFields as $v2) {
                $fieldData = $v2->getFieldData();
                $fieldData['title'] = $translator->translateI18n($fieldData['title']);
                $fieldData['field_type_name'] = $fieldTypes[$fieldData['field_type_id']];
                $row['fields'][] = $fieldData;
            }

            $fieldGroups[] = $row;
        }  
        
        return $fieldGroups;
    }
}