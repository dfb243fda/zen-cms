<?php

namespace App\ObjectType;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use App\FieldsGroup\FieldsGroup;
use Zend\Db\Sql\Sql;
use Zend\Form\Form;


class ObjectType implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $db;
    
    protected $id;
    
    protected $objectTypesTable = 'object_types';
    
    protected $translator;
    
    protected $objectTypesCollection;
    
    protected $guid;
    
    protected $name;
    
    protected $parentId;
    
    protected $isGuidable;
    
    protected $pageTypeId;    
    
    protected $pageContentTypeId;
    
    protected $isLocked;
    
    protected $objectFieldGroupsTable = 'object_field_groups';
    
    protected $fieldGroups;
    
    protected $initialized;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function init()
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;
        
        $this->translator = $this->serviceManager->get('translator');
        $this->objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $this->db = $this->serviceManager->get('db');
        
        $sqlRes = $this->db->query('select * from ' . DB_PREF . $this->objectTypesTable . ' where id = ?', array($this->id))->toArray();
                
        if (empty($sqlRes)) {
            throw new \Exception('There is no object type ' . $this->id);
        }
        else {
            $this->guid = $sqlRes[0]['guid'];
            $this->name = $sqlRes[0]['name'];            
            $this->parentId = $sqlRes[0]['parent_id'];
            $this->isGuidable = (bool)$sqlRes[0]['is_guidable'];
            $this->pageTypeId = $sqlRes[0]['page_type_id'];
            $this->pageContentTypeId = $sqlRes[0]['page_content_type_id'];
            $this->isLocked = (bool)$sqlRes[0]['is_locked'];
            
            $query = '
                SELECT
                    ofg.id as groupId, of.*
                FROM ' . DB_PREF . $this->objectFieldGroupsTable . ' ofg, ' . DB_PREF . 'fields_controller fc, ' . DB_PREF . 'object_fields of
                WHERE ofg.object_type_id = ? AND fc.group_id = ofg.id AND of.id = fc.field_id
                ORDER BY ofg.sorting ASC, fc.sorting ASC
            ';
            $resultSet = $this->db->query($query, array($this->id));
            $sqlRes = $resultSet->toArray();
                        
            $objectFields = array();            
            foreach ($sqlRes as $row) {
                if (!isset($objectFields[$row['groupId']])) {
                    $objectFields[$row['groupId']] = array();
                }
                $objectFields[$row['groupId']][] = $row;
            }
            
            $query = 'select * from ' . DB_PREF . $this->objectFieldGroupsTable . ' where object_type_id = ? order by sorting';
            $resultSet = $this->db->query($query, array($this->id));
            $sqlRes = $resultSet->toArray();
            
            $this->fieldGroups = array();
            foreach ($sqlRes as $row) {
                $fieldsGroup = new FieldsGroup(array(
                    'serviceManager' => $this->serviceManager,
                    'id' => $row['id'],
                    'groupData' => $row,
                ));
                if (!isset($objectFields[$row['id']])) {
                    $objectFields[$row['id']] = array();
                }                
                $fieldsGroup->loadFields($objectFields[$row['id']]);
                
                $this->fieldGroups[$row['id']] = $fieldsGroup;
            }
        }
    }
    
    public function save()
    {
        $sql = new Sql($this->db);
        
        $update = $sql->update(DB_PREF . $this->objectTypesTable);
        
        $update->set(array(
            'guid' => $this->guid,
            'name' => $this->name,
            'parent_id' => $this->parentId,
            'is_guidable' => (int)$this->isGuidable,
            'page_type_id' => $this->pageTypeId,
            'page_content_type_id' => $this->pageContentTypeId,
            'is_locked' => (int)$this->isLocked,
        ))->where('id = ' . (int)$this->id);
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    public function getData()
    {
        return array(
            'id' => $this->id,
            'guid' => $this->guid,
            'name' => $this->getName(),
            'parent_id' => $this->parentId,
            'is_guidable' => (int)$this->isGuidable,
            'page_type_id' => $this->pageTypeId,
            'page_content_type_id' => $this->pageContentTypeId,
            'is_locked' => (int)$this->isLocked,
        );
    }
        
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($typeId)
    {
        $this->id = $typeId;        
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
    
    public function getName()
    {
        return $this->translator->translateI18n($this->name);;
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
        if (($fieldsGroup = $this->getFieldsGroupByName($name)) !== null) {
            return $fieldsGroup->getId();
        }
        
        $query = 'SELECT MAX(sorting) AS max_sorting FROM ' . DB_PREF . $this->objectFieldGroupsTable . ' WHERE object_type_id = ?';
        $resultSet = $this->db->query($query, array($this->id));
        $sqlRes = $resultSet->toArray();
        
        $maxSorting = $sqlRes[0]['max_sorting'];
        if ($maxSorting) {
            $sorting = (int)$maxSorting + 1;
        }
        else {
            $sorting = 1;
        }
        
        $sql = new Sql($this->db);        
        $insert = $sql->insert(DB_PREF . $this->objectFieldGroupsTable);        
        $insert->values(array(
            'object_type_id' => $this->id,
            'name' => $name,
            'title' => $title,
            'is_locked' => 0,
            'sorting' => $sorting,
        ));        
        $sql->prepareStatementForSqlObject($insert)->execute();        
        
        $fieldGroupId = $this->db->getDriver()->getLastGeneratedValue();
                
        $children = $this->objectTypesCollection->getChildrenTypeIds($this->id);
        
        if (!empty($children)) {
            foreach ($children as $id) {
                if (null !== ($tmpObjectType = $this->objectTypesCollection->getType($id))) {
   //                 if ($tmpObjectType->isExists()) {
                        $tmpObjectType->addFieldsGroup($name, $title);
     //               }                    
                }
                else {
                    throw new Zend_Exception('there is no object type ' . $id);
                }
            }            
        }
        
        return $fieldGroupId;
    }
    
    public function delFieldsGroup($groupId)
    {
        $groupId = (int)$groupId;
        if ($this->isFieldsGroupExists($groupId)) {            
            $sql = new Sql($this->db);
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
    
    public function getForm()
    {
        $form = $this->serviceManager->get('App\ObjectType\Form');
        
        $form->setObjectType($this)->create();      
        
        return $form;
    }
    
    
    
}