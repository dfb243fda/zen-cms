<?php

namespace App\Object;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
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
    
    public function init()
    {        
        $this->fieldsGroupCollection = $this->serviceManager->get('App\Field\FieldsGroupCollection');        
        $this->fieldsGroupCollection->setObjectTypeId($this->typeId)->init();
    }
    
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
        ))->where('id = ' . (int)$this->typeId);
        
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
    
    public function getId()
    {
        return $this->typeId;
    }
    
    public function setId($typeId)
    {
        $this->typeId = $typeId;        
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
    
    public function getFieldsGroupCollection()
    {
        return $this->fieldsGroupCollection;
    }
    
    public function addFieldsGroup($name, $title)
    {
        return $this->fieldsGroupCollection->addFieldsGroup($name, $title);
    }
    
    public function delFieldsGroup($groupId)
    {
        return $this->fieldsGroupCollection->delFieldsGroup($groupId);
    }
    
    public function isFieldsGroupExists($groupId)
    {
        return $this->fieldsGroupCollection->isFieldsGroupExists($groupId);
    }
    
    public function getFieldsGroup($groupId)
    {
        return $this->fieldsGroupCollection->getFieldsGroup($groupId);
    }
    
    public function getFieldsGroupByName($name)
    {
        return $this->fieldsGroupCollection->getFieldsGroupByName($name);
    }
    
    public function getFieldGroups()
    {
        return $this->fieldsGroupCollection->getFieldGroups();
    }
    
    public function getForm($onlyVisible = false, $withName = false)
    {
        $formElementManager = $this->serviceManager->get('FormElementManager');
        
        $form = $formElementManager->get('App\Object\ObjectType\ObjectTypeForm', array(
            'objectType' => $this, 
            'onlyVisible' => $onlyVisible, 
            'withName' => $withName
        ));
        
        return $form;
    }
}
