<?php

exit('ooooppppppaaaa');

namespace App\ObjectsAdmin;

use Zend\Form\Factory;

use Zend\Db\Sql\Sql;

class ObjectsAdmin
{
    protected $serviceManager;
    
    protected $db;
    
    protected $guids;
    
    protected $includeDescendants;
    
    protected $baseForm;
    
    protected $objectTypeId;
    
    protected $parentObjectId;
    
    protected $objectId;
        
    public function __construct($options)
    {   
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        elseif (!is_array($options)) {
            throw new Zend_Exception('Invalid options provided; must be location of config file, a config object, or an array');
        }
        
        $this->setOptions($options);     
        
        if (null === $this->db) {
            $this->db = $this->serviceManager->get('db');
        }
                
        $this->objectsCollection = $this->serviceManager->get('objectsCollection');
        $this->objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $this->objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
    }
    
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setServiceManager($sm)
    {
        $this->serviceManager = $sm;
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
    
    public function setGuids($guids)
    {
        $this->guids = $guids;
        return $this;
    }
    
    protected function _getTypeIds($guids)
    {
        $typeIds = array();
        
        foreach ($guids as $guid) {
            $objectType = $this->objectTypesCollection->getType($guid);
            $typeIds[] = $objectType->getId();
            
            if ($this->includeDescendants) {
                $descendantTypeIds = $this->objectTypesCollection->getDescendantTypeIds($objectType->getId());
                $typeIds = array_merge($typeIds, $descendantTypeIds);
            }            
        }
        
        return $typeIds;
    }
    
    public function getItems($parentId)
    {
        $items = array();
        
        $typeIds = $this->_getTypeIds($this->guids);        
        
        $typeIdsStr = implode(', ', $typeIds);
        
        $sqlRes = $this->db->query('
                SELECT t1.*, 
                    (SELECT count(t2.id) FROM ' .DB_PREF . 'objects t2 WHERE t2.parent_id=t1.id) AS children_cnt
                FROM ' . DB_PREF . 'objects t1 
                WHERE t1.type_id IN (' . $typeIdsStr . ') AND t1.parent_id = ?
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
    
    public function setBaseForm($baseForm)
    {
        $this->baseForm = $baseForm;
        return $this;
    }
    
    public function setObjectTypeId($id)
    {
        $this->objectTypeId = $id;
        return $this;
    }
    
    public function setParentObjectId($id)
    {
        $this->parentObjectId = $id;
        return $this;
    }
    
    public function setObjectId($id)
    {
        $this->objectId = $id;
        return $this;
    }
    
    public function editObject($data)
    {
        $objectId = $this->objectId;
        $objectTypeId = $this->objectTypeId;
        $object = $this->objectsCollection->getObject($objectId);  
        
        $result = array();
        
        
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
        $formConfig = $objectType->getAppFormConfig($this->baseForm);
               
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);         
        foreach ($data as $k=>$v) {
            if (isset($v['name']) && '' == $v['name']) {
                $data[$k]['name'] = $this->translator->translate('(Without name)');
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
            $result['formMessages'] = $form->getMessages();
            $result['formValues'] = $form->getData();
        }
        
        return $result;        
    }
    
    public function addObject($data)
    {       
        $objectTypeId = $this->objectTypeId;
        $parentObjectId = $this->parentObjectId;
            
        $result = array();
        
        $objectType = $this->objectTypesCollection->getType($objectTypeId);
        $formConfig = $objectType->getAppFormConfig($this->baseForm);
              
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);         
        foreach ($data as $k=>$v) {
            if (isset($v['name']) && '' == $v['name']) {
                $data[$k]['name'] = $this->translator->translate('(Without name)');
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
            $result['formMessages'] = $form->getMessages();
            $result['formValues'] = $form->getData();
        }
        
        return $result;        
    }
    
    
    public function getForm($data = array())
    {
        $objectTypeId = $this->objectTypeId;
        
        if (null === $this->objectId) {                        
            $objectType = $this->objectTypesCollection->getType($objectTypeId);
            $formConfig = $objectType->getAppFormConfig($this->baseForm);
            
            $formValues = array();                        
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {                    
                    if (isset($data[$k2])) {
                        $formValues[$k][$k2] = $data[$k2];
                    }          
                }
            }
        } else {
            $objectType = $this->objectTypesCollection->getType($objectTypeId);
            $formConfig = $objectType->getAppFormConfig($this->baseForm);
            
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
    
}