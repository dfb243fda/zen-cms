<?php

namespace ImageGallery\Model;

use Zend\Form\Factory;

class ImageGallery
{
    protected $serviceManager;
    
    const RUBRIC = 0;
    const ITEM = 1;
    
    protected $rubricTypeIds;
    protected $itemTypeIds;
    
    protected $rubricGuid = 'image-gallery';
    protected $itemGuid   = 'image';
    
    protected $galleryType;
    
    protected $objectId;
    protected $parentObjectId;
    
    protected $objectTypeId;
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        $this->db = $sm->get('db');
        $this->translator = $sm->get('translator');
        $this->objectTypesCollection = $sm->get('objectTypesCollection');
        $this->objectsCollection = $sm->get('objectsCollection');
        $this->objectPropertyCollection = $sm->get('objectPropertyCollection');
    }
    
    public function getRubricGuid()
    {
        return $this->rubricGuid;
    }
    
    public function getItemGuid()
    {
        return $this->itemGuid;
    }
    
    public function isObjectRubric($objectId)
    {            
        $object = $this->objectsCollection->getObject($objectId);
        
        if ($object->isExists()) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getRubricTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    public function isObjectItem($objectId)
    {            
        $object = $this->objectsCollection->getObject($objectId);
        
        if ($object->isExists()) {
            $objectTypeId = $object->getTypeId();            
            $typeIds = $this->getItemTypeIds();            
            return in_array($objectTypeId, $typeIds);
        }
        return false;        
    }
    
    protected function getTypeIds()
    {
        return array_merge($this->getRubricTypeIds(), $this->getItemTypeIds());
    }
    
    protected function getRubricTypeIds()
    {
        if (null === $this->rubricTypeIds) {
            $typeIds = array();
            $objectType = $this->objectTypesCollection->getType($this->rubricGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $this->objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->rubricTypeIds = $typeIds;
        }
        return $this->rubricTypeIds;
    }
    
    protected function getItemTypeIds()
    {
        if (null === $this->itemTypeIds) {
            $typeIds = array();
            $objectType = $this->objectTypesCollection->getType($this->itemGuid);
            $typeIds[] = $objectType->getId();
            $descendantTypeIds = $this->objectTypesCollection->getDescendantTypeIds($objectType->getId());
            $typeIds = array_merge($typeIds, $descendantTypeIds);
            
            $this->itemTypeIds = $typeIds;
        }
        return $this->itemTypeIds;
    }
    
    public function setGalleryType($type)
    {
        $this->galleryType = $type;
        return $this;
    }
    
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
    }
    
    public function setParentObjectId($objectId)
    {
        $this->parentObjectId = $objectId;
        return $this;
    }
    
    public function getObjectTypeId()
    {
        if (null !== $this->objectTypeId) {
            return $this->objectTypeId;
        }
        
        if (null !== $this->objectId) {
            $object = $this->objectsCollection->getObject($this->objectId);   
            if ($object->isExists()) {
                $this->objectTypeId = $object->getTypeId();
                return $this->objectTypeId;
            }
        }
        
        return null;
    }
    
    public function getForm()
    {        
        $baseForm = $this->getBaseFormConfig();
        
        $objectTypeId = $this->getObjectTypeId();
        
        if (null === $this->objectId) {      
            $data = array(
                'type_id' => $objectTypeId,
            );
            
            $objectType = $this->objectTypesCollection->getType($objectTypeId);
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
            $object = $this->objectsCollection->getObject($this->objectId);
            $data = array(
                'name' => $object->getName(),
                'type_id' => $this->getObjectTypeId(),
            );
            
            $objectType = $this->objectTypesCollection->getType($objectTypeId);
            $formConfig = $objectType->getAppFormConfig($baseForm);
            
            $formValues = array();                        
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {
                    if ('field_' == substr($k2, 0, 6)) {
                        $fieldId = substr($k2, 6);
                        $property = $this->objectPropertyCollection->getProperty($this->objectId, $fieldId); 
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
    
    public function isObjectTypeCorrect($objectTypeId)
    {
        if (self::RUBRIC == $this->galleryType) {
            $typeIds = $this->getRubricTypeIds();
        } else {
            $typeIds = $this->getItemTypeIds();
        }
        
        return in_array($objectTypeId, $typeIds);
    }
        
    protected function getBaseFormConfig()
    {        
        if (self::RUBRIC == $this->galleryType) {
            $typeIds = $this->getRubricTypeIds();
        } else {
            $typeIds = $this->getItemTypeIds();
        }
        
        $objectTypesMultiOptions = array();
        foreach ($typeIds as $id) {
            $objectType = $this->objectTypesCollection->getType($id);
            $objectTypesMultiOptions[$id] = $objectType->getName();
        }        
        
        $baseFormConfig = array(
            'fieldsets' => array(
                'common' => array(
                    'spec' => array(
                        'name' => 'common',
                        'options' => array(
                            'label' => $this->translator->translate('Common params'),
                        ),
                        'elements' => array(
                            'type_id' => array(
                                'spec' => array(
                                    'type' => 'ObjectTypeLink',
                                    'name' => 'type_id',
                                    'options' => array(
                                        'label' => $this->translator->translate('Data type'),
                                        'value_options' => $objectTypesMultiOptions,
                                    ),
                                    'attributes' => array(
                                        'id' => 'object_type_id',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        
        return $baseFormConfig;
    }
    
    public function edit($data)
    {
        $data = (array)$data;
        
        $objectId = $this->objectId;
        $objectTypeId = $this->getObjectTypeId();
        $object = $this->objectsCollection->getObject($objectId);  
        
        $result = array();
        
        
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
        $formConfig = $objectType->getAppFormConfig($this->getBaseFormConfig());
               
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);         
        foreach ($data as $k=>$v) {
            if (isset($v['name']) && '' == $v['name']) {
                $data[$k]['name'] = $this->translator->translate('ImageGallery:(Without name)');
            }
        }

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
        
            $object->setName($insertBase['name'])->setTypeId($objectTypeId)->save();

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
            
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
    }
    
    public function add($data)
    {
        $data = (array)$data;
        
        $objectTypeId = $this->getObjectTypeId();
        $parentObjectId = $this->parentObjectId;
            
        $result = array();
        
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
        $formConfig = $objectType->getAppFormConfig($this->getBaseFormConfig());
              
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);         
        foreach ($data as $k=>$v) {
            if (isset($v['name']) && '' == $v['name']) {
                $data[$k]['name'] = $this->translator->translate('ImageGallery:(Without name)');
            }
        }
        
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
        
            $sqlRes = $this->db->query('select max(sorting) as max_sorting from ' . DB_PREF . 'objects WHERE parent_id = ? AND type_id = ?', array($parentObjectId, $objectTypeId))->toArray();
            
            if (empty($sqlRes)) {
                $sorting = 0;
            } else {
                $sorting = $sqlRes[0]['max_sorting'] + 1;
            }
            
            $objectId = $this->objectsCollection->addObject($insertBase['name'], $objectTypeId, $parentObjectId, $sorting);

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
            
            $result['objectId'] = $objectId;
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
    }
    
    public function del($objectId)
    {
        if ($this->isObjectRubric($objectId) || $this->isObjectItem($objectId)) {
            return $this->objectsCollection->delObject($objectId);
        }
        return false;
    }
    
    public function getItems($parentId)
    {
        $items = array();
        
        $typeIds = $this->getTypeIds();        
        
        $typeIdsStr = implode(', ', $typeIds);
        
        $sqlRes = $this->db->query('
                SELECT t1.*, 
                    (SELECT count(t2.id) FROM ' .DB_PREF . 'objects t2 WHERE t2.parent_id=t1.id and t2.is_deleted=0) AS children_cnt
                FROM ' . DB_PREF . 'objects t1 
                WHERE t1.type_id IN (' . $typeIdsStr . ') AND t1.parent_id = ? AND t1.is_deleted = 0
                ORDER BY t1.sorting    
                ', array($parentId))
                ->toArray();
        
        foreach ($sqlRes as $row) {
            if ($row['children_cnt'] > 0) {
                $row['state'] = 'closed';
            }
            else {
                $row['state'] = 'open';
            }        
            $items[] = $row;
        }
        
        return $items;
    }
}
