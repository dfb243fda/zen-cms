<?php

namespace Menu\Collection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class MenuCollection implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $objectTypeId;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function addMenu($data)
    {
        $db = $this->serviceManager->get('db');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $menuService = $this->serviceManager->get('Menu\Service\Menu');
        
        $insertFields = array();
        $insertBase = array();

        foreach ($data as $groupKey=>$groupData) {
            foreach ($groupData as $fieldName=>$fieldVal) {
                if ('field_' == substr($fieldName, 0, 6)) {
                    $insertFields[substr($fieldName, 6)] = $fieldVal;
                } else {
                    $insertBase[$fieldName] = $fieldVal;
                }
            }
        }
        
        $objectTypeId = $insertBase['type_id'];
        $objectType = $objectTypesCollection->getType($objectTypeId);
        
        $typeIds = $menuService->getMenuTypeIds();
        $typeIds = array_map(function ($id) use ($db) {
            return $db->getPlatform()->quoteValue($id);
        }, $typeIds);
        
        $typeIdsStr = implode($typeIds);

        $sqlRes = $db->query('
            select max(sorting) as max_sorting 
            from ' . DB_PREF . 'objects 
            where type_id IN (' . $typeIdsStr . ')', array())->toArray();

        if (empty($sqlRes)) {
            $sorting = 0;
        } else {
            $sorting = $sqlRes[0]['max_sorting'] + 1;
        }

        $objectId = $objectsCollection->addObject($insertBase['name'], $objectTypeId, 0, $sorting);

        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (array_key_exists($k2, $insertFields)) {
                    $property = $objectPropertyCollection->getProperty($objectId, $k2); 
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }

        return $objectId;
    }
    
    public function getForm($populateForm)
    {
        $formFactory = $this->serviceManager->get('Menu\FormFactory\MenuFormFactory');
        $formFactory->setObjectTypeId($this->objectTypeId)
                    ->setPopulateForm($populateForm);
        return $formFactory->getForm();
    }
}