<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Form\Factory;
use App\FieldsGroup\FieldsGroup;

use App\Field\Field;

class AddObjectType extends AbstractMethod
{
    protected $extKey = 'ObjectTypes';
    
    protected $translator;
    
    protected $db;
    
    protected $fieldsCollection;
    
    protected $objectTypesCollection;
    
    protected $objectTypesTable = 'object_types';
    
    public function init()
    {
        $this->rootServiceLocator = $this->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->fieldsCollection = $this->rootServiceLocator->get('fieldsCollection');
        $this->objectTypesCollection = $this->rootServiceLocator->get('objectTypesCollection');
    }
    
    public function main()
    {
        $result = array();
        
        $parentId = (int)$this->params()->fromRoute('id', 0);
        
        if (0 != $parentId) {
            if (null === $this->objectTypesCollection->getType($parentId)) {
                $result['success'] = false;
                $result['errMsg'] = 'Тип данных ' . $parentId . ' не найден';
                return $result;
            }
        }
        
        $objectTypeId = $this->objectTypesCollection->addType($parentId, $this->translator->translate('ObjectTypes:New object type'));

        $this->redirect()->toRoute('admin/method', array(
            'module' => 'ObjectTypes',
            'method' => 'EditObjectType',
            'id'     => $objectTypeId
        ));
        
        $result['success'] = true;
        return $result;
    }
}