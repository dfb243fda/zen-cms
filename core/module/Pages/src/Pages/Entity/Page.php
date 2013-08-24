<?php

namespace Pages\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;

class Page implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $pageTypeId;
    
    protected $pageId;
    
    protected $formFactory;
        
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($typeId)
    {
        $this->objectTypeId = $typeId;
        return $this;
    }
    
    public function setPageTypeId($typeId)
    {
        $this->pageTypeId = $typeId;
        return $this;
    }
    
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
        return $this;
    }
    
    public function getForm()
    {                
        $this->formFactory = $formFactory = $this->serviceManager->get('Pages\FormFactory\Page');
        
        $formFactory->setPageTypeId($this->pageTypeId)
                    ->setObjectTypeId($this->objectTypeId)
                    ->setPageId($this->pageId);
        
        $form = $formFactory->getForm();
        
        return $form;
    }
    
    public function getPageData()
    {
        return $this->formFactory->getPageData();
    }
    
    public function editPage($data)
    {        
        $pageId = $this->pageId;
        
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $db = $this->serviceManager->get('db');
        
        $insertFields = array(); //значения для свойств объектов (такблица object и т.д.)
        $insertBase = array(); // значения для таблицы pages

        foreach ($data as $groupKey=>$groupData) {
            foreach ($groupData as $fieldName=>$fieldVal) {
                if ('field_' == substr($fieldName, 0, 6)) {
                    $insertFields[substr($fieldName, 6)] = $fieldVal;
                } else {
                    if (is_array($fieldVal)) {
                        $fieldVal = implode(',', $fieldVal);
                    }                        
                    $insertBase[$fieldName] = $fieldVal;
                }
            }
        }

        $tmp = $this->getPageData();
        $objectId = $tmp['object_id'];
        $domainId = $tmp['domain_id'];

        $objectTypeId = $insertBase['object_type_id'];

        $object = $objectsCollection->getObject($objectId);            
        $object->setName($insertBase['name'])->setTypeId($objectTypeId)->save();


        unset($insertBase['name']);
        unset($insertBase['object_type_id']);

        if ($insertBase['is_default']) {
            $db->query('update ' . DB_PREF . 'pages set is_default = 0 where domain_id = ?',  array($domainId));
        }
        if ($insertBase['is_403']) {
            $db->query('update ' . DB_PREF . 'pages set is_403 = 0 where domain_id = ?',  array($domainId));
        }
        if ($insertBase['is_404']) {
            $db->query('update ' . DB_PREF . 'pages set is_404 = 0 where domain_id = ?',  array($domainId));
        }
        
        $sql = new Sql($db);
        
        $update = $sql->update(DB_PREF . 'pages')->set($insertBase)->where('id = ' . (int)$pageId);
        $sql->prepareStatementForSqlObject($update)->execute();    

        $objectType = $objectTypesCollection->getType($objectTypeId); 
        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (isset($insertFields[$k2])) {
                    $property = $objectPropertyCollection->getProperty($objectId, $k2);                        
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }

        return true;
    }
}