<?php

namespace App\Field;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FieldType implements ServiceManagerAwareInterface
{
    protected $serviceManager;
           
    protected $typeId;
    
    protected $typeData;
    
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
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
    
    public function setTypeData($typeData)
    {
        $this->typeData = $typeData;
        return $this;
    }
        
    public function getIsMultiple()
    {
        return $this->typeData['is_multiple'];
    }
    
    public function getName()
    {
        return $this->typeData['name'];
    }
    
    public function getTitle() 
    {
        $translator = $this->serviceManager->get('translator');
        return $translator->translateI18n($this->typeData['title']);
    }
}
