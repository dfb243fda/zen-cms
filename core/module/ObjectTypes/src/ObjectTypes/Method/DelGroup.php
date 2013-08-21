<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Form\Factory;
use App\FieldsGroup\FieldsGroup;

use App\Field\Field;

class DelGroup extends AbstractMethod
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
            'success' => false,
        );
        if (null === $this->request->getPost('groupId') || null === $this->request->getPost('objectTypeId')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {
            $objectTypeId = (int)$this->request->getPost('objectTypeId');
            $groupId = (int)$this->request->getPost('groupId');
            
            $type = $this->objectTypesCollection->getType($objectTypeId);
                
            if (false === $type) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не найден';
                return $result;
            }
            else {
                if ($type->delFieldsGroup($groupId)) {
                    $result['msg'] = 'Группа успешно удалена';
                    $result['success'] = 1;
                } else {
                    $result['errMsg'] = 'При удалении группы произошли ошибки';
                }
            }
        }
        return $result;
    }
    
}