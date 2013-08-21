<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Form\Factory;
use App\FieldsGroup\FieldsGroup;

use App\Field\Field;

class DelField extends AbstractMethod
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
        
        if (null !== $this->request->getPost('fieldId') &&
                null !== $this->request->getPost('objectTypeId') && 
                null !== $this->request->getPost('groupId'))
        {        
            $fieldId = (int)$this->request->getPost('fieldId');
            $objectTypeId = (int)$this->request->getPost('objectTypeId');
            $groupId = (int)$this->request->getPost('groupId');            

            $this->objectTypesCollection->getType($objectTypeId)->getFieldsGroup($groupId)->detachField($fieldId);
            
            $result['msg'] = 'Поле успешно удалено';
            $result['success'] = 1;
        } else {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        }
        
        return $result;
    }
}