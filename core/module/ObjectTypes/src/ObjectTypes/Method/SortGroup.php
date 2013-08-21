<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Form\Factory;
use App\FieldsGroup\FieldsGroup;

use App\Field\Field;

class SortGroup extends AbstractMethod
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
        
        if (null !== $this->request->getPost('group') && null !== $this->request->getPost('groupBefore')) {
            $groupId = (int)$this->request->getPost('group');   
            $groupBeforeId = (int)$this->request->getPost('groupBefore');
            
            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $this->rootServiceLocator,
                'id' => $groupId,
            )); 
            
            if ($fieldsGroup->isExists()) {
                $result['success'] = (int)$fieldsGroup->moveGroupAfter($groupBeforeId);                
            }
        }
        
        return $result;
    }
}