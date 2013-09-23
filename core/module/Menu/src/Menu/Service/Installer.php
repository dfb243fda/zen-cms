<?php

namespace Menu\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Installer implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function install()
    {
        $sm = $this->serviceManager;
        
        $objectTypesCollection = $sm->get('objectTypesCollection');
        $fieldsCollection = $sm->get('fieldsCollection');
        $fieldTypesCollection = $sm->get('fieldTypesCollection');

        $menuGuid = 'menu';
        if (null === ($id = $objectTypesCollection->getTypeIdByGuid($menuGuid))) {  
            $id = $objectTypesCollection->addType(0, 'i18n::Menu:Menu object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($menuGuid)->setIsGuidable(true)->save();
        }
        $menuObjectTypeId = $id;
        
        $menuItemGuid = 'menu-item';
        if (null === $objectTypesCollection->getTypeByGuid($menuItemGuid)) {  
            $id = $objectTypesCollection->addType(0, 'i18n::Menu:Menu item object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($menuItemGuid)->save();
            
            $groupId = $objectType->addFieldsGroup('item-info', 'i18n::Menu:Item info fields group');
                
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('pageLink');

            $fieldsGroup = $objectType->getFieldsGroup($groupId);

            $fieldId = $fieldsCollection->addField(array(
                'name' => 'page',
                'title' => 'i18n::Menu:Page id',
                'field_type_id' => $fieldTypeId,
                'tip' => '',
                'is_locked' => 0,
                'is_visible' => 1,
                'is_required' => 1,
                'in_filter' => 0,
                'in_search' => 0,
                'guide_id' => false,
            ));

            $fieldsGroup->attachField($fieldId);
        }
        
        $db = $sm->get('db');
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('Menu', 'SingleMenu'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $menuItemGuid = 'menu-content-object-type';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($menuItemGuid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::Menu:Menu content object type');
                $objectType = $objectTypesCollection->getType($id);
         
                $objectType->setPageContentTypeId($contentTypeId);
                    
                $objectType->setGuid($menuItemGuid)->save();


                $groupId = $objectType->addFieldsGroup('item-info', 'i18n::Menu:Item info fields group');

                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('select');

                $fieldsGroup = $objectType->getFieldsGroup($groupId);
                                
                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'menu_id',
                    'title' => 'i18n::Menu:Menu id',
                    'field_type_id' => $fieldTypeId,
                    'tip' => '',
                    'is_locked' => 0,
                    'is_visible' => 1,
                    'is_required' => 1,
                    'in_filter' => 0,
                    'in_search' => 0,
                    'guide_id' => $menuObjectTypeId,
                ));

                $fieldsGroup->attachField($fieldId);
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }    
        
    }
}