<?php

namespace App\Object;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use App\FieldsGroup\FieldsGroup;
use Zend\Db\Sql\Sql;

class ObjectType implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $typeId;
    
    protected $guid;
    
    protected $name;
    
    protected $parentId;
    
    protected $isGuidable;
    
    protected $pageTypeId;    
    
    protected $pageContentTypeId;
    
    protected $isLocked;  
    
    protected $objectTypesTable = 'object_types';
    
    protected $objectFieldGroupsTable = 'object_field_groups';
        
    protected $fieldGroups;
    
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function save()
    {
        $db = $this->serviceManager->get('db');
        
        $sql = new Sql($db);
        
        $update = $sql->update(DB_PREF . $this->objectTypesTable);
        
        $update->set(array(
            'guid' => $this->guid,
            'name' => $this->name,
            'parent_id' => $this->parentId,
            'is_guidable' => (int)$this->isGuidable,
            'page_type_id' => $this->pageTypeId,
            'page_content_type_id' => $this->pageContentTypeId,
            'is_locked' => (int)$this->isLocked,
        ))->where('id = ' . (int)$this->objectId);
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    public function getData()
    {
        return array(
            'id' => $this->typeId,
            'guid' => $this->guid,
            'name' => $this->getName(),
            'parent_id' => $this->parentId,
            'is_guidable' => (int)$this->isGuidable,
            'page_type_id' => $this->pageTypeId,
            'page_content_type_id' => $this->pageContentTypeId,
            'is_locked' => (int)$this->isLocked,
        );
    }
    
    public function init()
    {        
        $translator = $this->serviceManager->get('translator');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $db = $this->serviceManager->get('db');
        
        $query = '
            SELECT
                ofg.id as groupId, of.*
            FROM ' . DB_PREF . $this->objectFieldGroupsTable . ' ofg, ' . DB_PREF . 'fields_controller fc, ' . DB_PREF . 'object_fields of
            WHERE ofg.object_type_id = ? AND fc.group_id = ofg.id AND of.id = fc.field_id
            ORDER BY ofg.sorting ASC, fc.sorting ASC
        ';
        $resultSet = $db->query($query, array($this->typeId));
        $sqlRes = $resultSet->toArray();

        $objectFields = array();            
        foreach ($sqlRes as $row) {
            if (!isset($objectFields[$row['groupId']])) {
                $objectFields[$row['groupId']] = array();
            }
            $objectFields[$row['groupId']][] = $row;
        }

        $query = '
            select * 
            from ' . DB_PREF . $this->objectFieldGroupsTable . ' 
            where object_type_id = ? 
            order by sorting';
        
        $sqlRes = $db->query($query, array($this->typeId))->toArray();

        $this->fieldGroups = array();
        foreach ($sqlRes as $row) {
            $fieldsGroup = $this->serviceManager->get('App\Field\FieldGroup');
            
            if (!isset($objectFields[$row['id']])) {
                $objectFields[$row['id']] = array();
            }   
            
            $fieldsGroup->setId($row['id'])
                        ->setGroupData($row)
                        ->loadFields($objectFields[$row['id']]);
            
            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $this->serviceManager,
                'id' => $row['id'],
                'groupData' => $row,
            ));

            $this->fieldGroups[$row['id']] = $fieldsGroup;
        }
    }
        
    public function getId()
    {
        return $this->objectId;
    }
    
    public function setId($typeId)
    {
        $this->objectId = $typeId;        
        return $this;
    }
    
    public function getName()
    {
        $translator = $this->serviceManager->get('translator');
        return $translator->translateI18n($this->name);;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getIsLocked()
    {
        return $this->isLocked;
    }
    
    public function setIsLocked($isLocked)
    {
        $this->isLocked = (bool)$isLocked;
        return $this;
    }
    
    public function getParentId()
    {
        return $this->parentId;
    }
    
    public function setParentId($parentid)
    {
        $this->parentId = $parentid;
        return $this;
    }
    
    public function getIsGuidable()
    {
        return $this->isGuidable;
    }

    public function setIsGuidable($isGuidable)
    {
        $this->isGuidable = (bool)$isGuidable;
        return $this;
    }
    
    public function getGuid()
    {
        return $this->guid;
    }
    
    public function setGuid($guid)
    {
        $this->guid = $guid;
        return $this;
    }
    
    public function getPageTypeId()
    {
        return $this->pageTypeId;
    }
    
    public function setPageTypeId($id)
    {
        $this->pageTypeId = $id;
        return $this;
    }
    
    public function getPageContentTypeId()
    {
        return $this->pageContentTypeId;
    }
    
    public function setPageContentTypeId($id)
    {
        $this->pageContentTypeId = $id;
        return $this;
    }
    
    public function addFieldsGroup($name, $title)
    {
        $db = $this->serviceManager->get('db');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        if (($fieldsGroup = $this->getFieldsGroupByName($name)) !== null) {
            return $fieldsGroup->getId();
        }
        
        $query = '
            SELECT MAX(sorting) AS max_sorting 
            FROM ' . DB_PREF . $this->objectFieldGroupsTable . ' 
            WHERE object_type_id = ?';
        $resultSet = $db->query($query, array($this->typeId));
        $sqlRes = $resultSet->toArray();
        
        $maxSorting = $sqlRes[0]['max_sorting'];
        if ($maxSorting) {
            $sorting = (int)$maxSorting + 1;
        }
        else {
            $sorting = 1;
        }
        
        $sql = new Sql($db);        
        $insert = $sql->insert(DB_PREF . $this->objectFieldGroupsTable);        
        $insert->values(array(
            'object_type_id' => $this->typeId,
            'name' => $name,
            'title' => $title,
            'is_locked' => 0,
            'sorting' => $sorting,
        ));        
        $sql->prepareStatementForSqlObject($insert)->execute();        
        
        $fieldGroupId = $db->getDriver()->getLastGeneratedValue();
                
        $children = $objectTypesCollection->getChildrenTypeIds($this->typeId);
        
        if (!empty($children)) {
            foreach ($children as $id) {
                $tmpObjectType->addFieldsGroup($name, $title);
            }            
        }
        
        return $fieldGroupId;
    }
    
    public function delFieldsGroup($groupId)
    {
        $db = $this->serviceManager->get('db');
        
        $groupId = (int)$groupId;
        if ($this->isFieldsGroupExists($groupId)) {            
            $sql = new Sql($db);
            $delete = $sql->delete(DB_PREF . $this->objectFieldGroupsTable)->where('id = ' . (int)$groupId);        
            $sql->prepareStatementForSqlObject($delete)->execute();
            
            unset($this->fieldGroups[$groupId]);
            return true;
        } else {
            return false;
        }
    }
    
    private function isFieldsGroupExists($groupId)
    {
        return isset($this->fieldGroups[$groupId]);        
    }
    
    public function getFieldsGroup($groupId)
    {
        if (isset($this->fieldGroups[$groupId])) {
            return $this->fieldGroups[$groupId];
        }
        return null;
    }
    
    public function getFieldsGroupByName($name)
    {
        $fieldGroupsList = $this->getFieldGroups();
        foreach ($fieldGroupsList as $fieldsGroup) {
            if ($fieldsGroup->getName() == $name) {
                return $fieldsGroup;
            }
        }
        return null;
    }
    
    public function getFieldGroups()
    {
        return $this->fieldGroups;
    }
    
    public function getForm($onlyVisible = false)
    {
        $formElementManager = $this->serviceManager->get('FormElementManager');
        
        $form = $formElementManager->get('App\Object\ObjectType\ObjectTypeForm', array('objectType' => $this, 'onlyVisible' => $onlyVisible));
        
        return $form;
    }
}
