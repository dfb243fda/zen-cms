<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class DelObjectType extends AbstractMethod
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
        $result = array();
        
        if (null === $this->request->getPost('id')) {
            $result['success'] = false;
            $result['errMsg'] = 'Не передан id типа данных';
        }
        else {
            $id = (int)$this->request->getPost('id');
            
            $result = $this->objectTypesCollection->delType($id);
            
            if (true == $result['success']) {
                $result['msg'] = 'Тип данных успешно удален';
            }
        }
        
        return $result;
    }
    
}