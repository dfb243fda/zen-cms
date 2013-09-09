<?php

namespace App\Object;

use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class Object implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $objectId;
    
    protected $properties = array();
    
    protected $propGroups = array();
    
    protected $objectData;
    
    protected $objectsTable = 'objects';
    
    protected $objectTypesTable = 'object_types';
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function init()
    {        
        $objectType = $this->getType();

        $fieldGroupsList = $objectType->getFieldGroups();

        foreach ($fieldGroupsList as $group) {
            $fields = $group->getFields();

            $this->propGroups[$group->getId()] = array();
            foreach ($fields as $field) {
                $this->properties[$field->getId()] = $field->getName();
                $this->propGroups[$group->getId()][] = $field->getId();
            }
        }
    }
    
    public function getObjectData()
    {
        return $this->objectData;
    }
    
    public function setObjectData($objectData)
    {
        $this->objectData = $objectData;
        return $this;
    }
    
    public function getId()
    {
        return $this->objectId;
    }
    
    public function setId($id)
    {
        $this->objectId = $id;
        return $this;
    }
    
    public function getPropertyByName($name)
    {
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        
        $name = strtolower($name);
        
        foreach ($this->properties as $fieldId=>$value) {
            if (is_object($value)) {
                if ($value->getName() == $name) {
                    return $value;
                }
            } else {
                if (strtolower($value) == $name) {                    
                    $this->properties[$fieldId] = $objectPropertyCollection->getProperty($this->objectId, $fieldId);
                    return $this->properties[$fieldId];
                }
            }
        }
        
        return null;
    }
    
    public function getPropertyById($fieldId)
    {
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        
        if (isset($this->properties[$fieldId])) {
            if (!is_object($this->properties[$fieldId])) {
                $this->properties[$fieldId] = $objectPropertyCollection->getProperty($this->objectId, $fieldId);
            }
            
            return $this->properties[$fieldId];
        } else {
            return null;
        }
    }
    
    public function isPropertyExists($fieldId)
    {
        return isset($this->properties[$fieldId]);
    }

    
    public function isPropGroupExists($groupId)
    {
        return isset($this->propGroups[$groupId]);
    }
    
    public function getPropGroupIdByName($groupName)
    {
        $groupsList = $this->getType()->getFieldGroupsList();
        foreach ($groupsList as $group) {
            if ($group->getName() == $groupName) {
                return $group->getId();
            }
        }return false;
    }
    
    public function getPropGroupByName($groupName)
    {
        $id = $this->getPropGroupIdByName($groupName);
        if (null !== $id) {
            return $this->getPropGroupById($id);
        }
        return null;
    }

    public function getPropGroupById($id)
    {
        if (isset($this->propGroups[$id])) {
            return $this->propGroups[$id];
        }
        return null;
    }

    public function getValue($propertyName)
    {
        $property = $this->getPropertyByName($propertyName);
        
        if (null !== $property) {
            return $property->getValue();
        }
        return null;
    }

    public function setValue($propertyName, $val)
    {
        if ($property = $this->getPropertyByName($propertyName)) {
            return $property->setValue($val);
        }
        return false;
    }
    
    public function save()
    {
        $db = $this->serviceManager->get('db');
        
        $tmp = $this->objectData;
        unset($tmp['id']);
        
        $tmp['modified_time'] = time();
                
        $sql = new Sql($db);
        $update = $sql->update(DB_PREF . $this->objectsTable)->set($tmp)->where('id = ' . (int)$this->objectId);
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
                
        foreach ($this->properties as $property) {
            if (is_object($property)) {
                $property->save();
            }
        }
    }
    
    public function getName()
    {
        return $this->objectData['name'];
    }
    
    public function setName($name)
    {
        $this->objectData['name'] = $name;
        return $this;
    }
    
    public function getType()
    {
        $objectTypesCollection = $this->serviceManager->get('ObjectTypesCollection');        
        return $objectTypesCollection->getType($this->objectData['type_id']);
    }
    
    public function getTypeId()
    {
        return $this->objectData['type_id'];
    }
    
    public function setTypeId($typeId)
    {
        $this->objectData['type_id'] = $typeId;
        return $this;
    }
    
    public function getSorting()
    {
        return $this->objectData['sorting'];
    }
    
    public function setSorting($sorting)
    {
        $this->objectData['sorting'] = $sorting;
        return $this;
    }
    
    public function getParentId()
    {
        return $this->objectData['parent_id'];
    }
    
    public function setParentId($parentId)
    {
        $this->objectData['parent_id'] = $parentId;
        return $this;
    }
    
    public function getIsActive()
    {
        return (bool)$this->objectData['is_active'];
    }
    
    public function setIsActive($isActive)
    {
        $this->objectData['is_active'] = (int)$isActive;
        return $this;
    }
    
    public function getGuid()
    {
        return $this->objectData['guid'];
    }
    
    public function setGuid($guid)
    {
        $this->objectData['guid'] = $guid;
        return $this;
    }
}

