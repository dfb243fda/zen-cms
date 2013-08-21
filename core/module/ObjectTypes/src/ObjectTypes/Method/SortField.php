<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Form\Factory;
use App\FieldsGroup\FieldsGroup;

use App\Field\Field;

class SortField extends AbstractMethod
{
    protected $extKey = 'ObjectTypes';
    
    protected $translator;
    
    protected $db;
    
    protected $fieldsCollection;
    
    protected $objectTypesCollection;
    
    protected $objectTypesTable = 'object_types';
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->fieldsCollection = $this->rootServiceLocator->get('fieldsCollection');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {
        $result = array(
            'success' => 0,
        );
        
        if (null !== $this->request->getPost('field') &&
                null !== $this->request->getPost('fieldBefore') && 
                null !== $this->request->getPost('group') && 
                null !== $this->request->getPost('groupTarget')) {
            
            $fieldId = (int)$this->request->getPost('field');
            $fieldBeforeId = (int)$this->request->getPost('fieldBefore');
            $groupId = (int)$this->request->getPost('group');
            $groupTargetId = (int)$this->request->getPost('groupTarget');
                      
            $field =$this->fieldsCollection->getField($fieldId);
            
            if ($field->isExists()) {
                if ($field->moveFieldAfter($fieldBeforeId, $groupId, $groupTargetId)) {                    
                    $result['success'] = 1;
                }                
            }
        }
        
        return $result;
    }
}