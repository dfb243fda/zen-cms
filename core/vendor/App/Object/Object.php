<?php

namespace App\Object;

use Zend\Db\Sql\Sql;

class Object
{
    protected $serviceManager;
    
    protected $db;
    
    protected $id;
    
    protected $isExists;
    
    protected $objectData;
    
    protected $objectsTable = 'objects';
    
    protected $objectTypesTable = 'object_types';
    
    protected $objectType;
    
    protected $properties = array();
    
    protected $propGroups = array();
    
    
    
    /**
     * @var App_Bootstrap 
     */
    protected $_bootstrap = null;
    
    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */    
    protected $_db = null;
    
    protected $_isExists = null;
    
    protected $_id = null;
    
    protected $_objectData = null;
    
    protected $_objectsTable = 'objects';
    
    protected $_objectTypesTable = 'object_types';
    
    protected $_objectType = null;
    
    protected $_properties = array();
    
    protected $_propGroups = array();
    
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
        
        if (null === $this->id) {
            throw new Zend_Exception('Object id is undefined');
        }
        
        $this->translator = $this->serviceManager->get('translator');     
        $this->objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $this->objectsCollection = $this->serviceManager->get('objectsCollection');
        $this->objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        
        $this->init();
    }
    
    protected function init()
    {
        $this->isExists = true;
        if ($this->objectData === null) {
            $sqlResult = $this->db->query('
                SELECT o.*, t.guid AS type_guid
                FROM ' . DB_PREF . $this->objectsTable . ' o,
                    ' . DB_PREF . $this->objectTypesTable . ' t
                WHERE o.id = ?
                    AND o.is_deleted = 0
                    AND o.type_id = t.id
            ', array($this->id))->toArray();
            
            if (empty($sqlResult)) {
                $this->isExists = false;
            } else {                
                $this->objectData = $sqlResult[0];
            }
        }
        
        if ($this->isExists) {
            $this->objectType = $this->objectTypesCollection->getType($this->objectData['type_id']);
                
            $fieldGroupsList = $this->objectType->getFieldGroups();

            foreach ($fieldGroupsList as $group) {
                $fields = $group->getFields();

                $this->propGroups[$group->getId()] = array();
                foreach ($fields as $field) {
                    $this->properties[$field->getId()] = $field->getName();
                    $this->propGroups[$group->getId()][] = $field->getId();
                }
            }
        }
        
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
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function isExists()
    {
        return $this->isExists;
    }
    
    public function getPropertyByName($name)
    {
        $name = strtolower($name);
        
        foreach ($this->properties as $fieldId=>$value) {
            if (is_object($value)) {
                if ($value->getName() == $name) {
                    return $value;
                }
            } else {
                if (strtolower($value) == $name) {                    
                    $this->properties[$fieldId] = $this->objectPropertyCollection->getProperty($this->id, $fieldId);
                    return $this->properties[$fieldId];
                }
            }
        }
        
        return null;
    }
    
    public function getPropertyById($fieldId)
    {
        if (isset($this->properties[$fieldId])) {
            if (!is_object($this->properties[$fieldId])) {
                $this->properties[$fieldId] = $this->objectPropertyCollection->getProperty($this->id, $fieldId);
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
        $tmp = $this->objectData;
        unset($tmp['id']);
        unset($tmp['type_guid']);
        
        $tmp['modified_time'] = time();
                
        $sql = new Sql($this->db);
        $update = $sql->update(DB_PREF . $this->objectsTable)->set($tmp)->where('id = ' . (int)$this->id);
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
    
    public function getTypeId()
    {
        return $this->objectData['type_id'];
    }
    
    public function getObjectData()
    {
        return $this->objectData;
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