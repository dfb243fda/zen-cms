<?php

namespace App\FieldType;

class FieldType
{
    protected $serviceManager;
    
    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */    
    protected $_db = null;
    
    protected $_id = null;
    
    protected $_typeData = null;
    
    protected $_isExists = null;
    
    protected $_fieldTypesTable = 'object_field_types';
    
    public function __construct($options)
    {   
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        elseif (!is_array($options)) {
            throw new Zend_Exception('Invalid options provided; must be location of config file, a config object, or an array');
        }
        
        $this->setOptions($options);     
        
        if (null === $this->_db) {
            $this->_db = $this->serviceManager->get('db');
        }
        
        if (null === $this->_id) {
            throw new Zend_Exception('Field id is undefined');
        }
        
        $this->translator = $this->serviceManager->get('translator');
        $this->_objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $this->_init();
    }
    
    protected function _init()
    {
        $this->_isExists = true;
        if (null === $this->_typeData) {
            $sqlResult = $this->_db->select()
                    ->from(DB_PREF . $this->_fieldTypesTable)
                    ->where('id = ?', $this->_id)
                    ->query()
                    ->fetchAll();
            
            if (empty($sqlResult)) {            
                $this->_isExists = false;
                $this->_typeData = array();
            } else {                
                $this->_typeData = $sqlResult[0];
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
        $this->_db = $db;
        return $this;
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function setId($typeId)
    {
        $this->_id = $typeId;
        return $this;
    }
    
    public function setTypeData($fieldData)
    {
        $this->_typeData = $fieldData;
        return $this;
    }
        
    public function getIsMultiple()
    {
        return $this->_typeData['is_multiple'];
    }
    
    public function getName()
    {
        return $this->_typeData['name'];
    }
    
    public function getTitle() 
    {
        return $this->translator->translateI18n($this->_typeData['title']);
    }
    
    
}