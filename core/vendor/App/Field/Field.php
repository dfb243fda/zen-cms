<?php

namespace App\Field;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Db\Sql\Sql;

class Field implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $fieldId;
    
    protected $fieldData = array();  
        
    protected $fieldsTable = 'object_fields';
    
    protected $fieldsControllerTable = 'fields_controller';  
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getId()
    {
        return $this->fieldId;
    }
       
    public function setId($id)
    {
        $this->fieldId = $id;
        return $this;
    }    
    
    public function getFieldData()
    {
        return $this->fieldData;
    }
    
    public function setFieldData($fieldData)
    {
        $this->fieldData = $fieldData;
        return $this;
    }
    
    public function getName()
    {
        return $this->fieldData['name'];
    }
    
    public function getTitle()
    {
        return $this->fieldData['title'];
    }
    
    public function getTip()
    {
        return $this->fieldData['tip'];
    }
    
    public function getIsRequired()
    {
        return (boolean)$this->fieldData['is_required'];
    }
    
    public function getFieldTypeId()
    {
        return $this->fieldData['field_type_id'];
    }
    
    public function getGuideId()
    {
        return $this->fieldData['guide_id'];
    }
    
    public function getInSearch()
    {
        return (boolean)$this->fieldData['in_search'];
    }
    
    public function getIsVisible()
    {
        return (boolean)$this->fieldData['is_visible'];
    }
    
    public function getFieldTypeName()
    {
        $fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');
        return $fieldTypesCollection->getFieldType($this->fieldData['field_type_id'])->getName();
    }
    
    public function getFieldTypeTitle()
    {
        $fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');
        return $fieldTypesCollection->getFieldType($this->fieldData['field_type_id'])->getTitle();
    }
    
    public function getFieldTypeIsMultiple()
    {
        $fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');
        return $fieldTypesCollection->getFieldType($this->fieldData['field_type_id'])->getIsMultiple();
    }
    
    public function save()
    {
        $db = $this->serviceManager->get('db');
        
        $fieldData = $this->fieldData;
        unset($fieldData['id']);
     
        if (isset($fieldData['guide_id']) && !$fieldData['guide_id']) {
            $fieldData['guide_id'] = null;
        }
        
        $sql = new Sql($db);        
        $update = $sql->update(DB_PREF . $this->fieldsTable);        
        $update->set($fieldData)->where('id = ' . (int)$this->fieldId);        
        $sql->prepareStatementForSqlObject($update)->execute();
    }
    
    /**
     * @return \Zend\Form\Element
     */
    public function getZendFormElement()
    {        
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        $name = 'field_' . $this->fieldId;
        
        $spec = array(
   //         'type' => 'Zend\Form\Element\\' . ucfirst($this->getFieldTypeName()),
            'name' => $name,
            'type' => $this->getFieldTypeName(),
            'options' => array(
                'label' => $this->translator->translateI18n($this->getTitle()),
            ),
        );
        
        if ($this->getFieldTypeIsMultiple()) {
            $spec['attributes'] = array(
                'multiple' => true,
            );
        } else {
            $spec['options']['empty_option'] = '';
        }   
        
        
        if (null !== $this->getGuideId()) { 
            $sqlRes = $db->query('select * from ' . DB_PREF . 'objects where type_id = ?', array($this->getGuideId()));
            
            $spec['options']['value_options'] = array();
            foreach ($sqlRes as $row) {
                $spec['options']['value_options'][$row['id']] = $translator->translateI18n($row['name']);
            }
        }

        if ($this->getTip()) {
            $spec['options']['description'] = $translator->translateI18n($this->getTip());
        }        
                
        $formFactory = new \Zend\Form\Factory($this->serviceManager->get('formElementManager'));
                
        $element = $formFactory->create($spec);
        
        return $element;
    }
    
    public function moveFieldAfter($fieldBeforeId, $currentGroupId, $targetGroupId)
    {
        $db = $this->serviceManager->get('db');
        
        if (0 == $fieldBeforeId) {
            $newSorting = 0;
        }
        else {
            $query = 'SELECT sorting FROM ' . DB_PREF . $this->fieldsControllerTable . ' WHERE group_id = ? AND field_id = ?';

            $resultSet = $db->query($query, array($targetGroupId, $fieldBeforeId));
            $sqlRes = $resultSet->toArray();            
            
            if (empty($sqlRes)) {
                return false;
            }
            $newSorting = $sqlRes[0]['sorting'] + 1;
        }
        
        $db->query('UPDATE ' . DB_PREF . $this->fieldsControllerTable . '
            SET sorting = (sorting + 1)
            WHERE group_id = ? AND sorting >= ?', array($targetGroupId, $newSorting));
        
        $db->query('UPDATE ' . DB_PREF . $this->fieldsControllerTable . '
            SET sorting = ?, group_id = ?
            WHERE group_id = ? AND field_id = ?', array($newSorting, $targetGroupId, $currentGroupId, $this->fieldId));
        
        return true;
    }    
}