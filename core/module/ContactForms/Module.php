<?php

namespace ContactForms;

use App\FieldsGroup\FieldsGroup;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }
    
    public function onInstall($sm)
    {
        $db = $sm->get('db');
        
        $objectTypesCollection = $sm->get('objectTypesCollection');
        $fieldsCollection = $sm->get('fieldsCollection');
        $fieldTypesCollection = $sm->get('fieldTypesCollection');

        $guid = 'contact-form';

        $contactFormObjectTypeId = $objectTypesCollection->getTypeIdByGuid($guid);
        if (null === $contactFormObjectTypeId) {
            $contactFormObjectTypeId = $objectTypesCollection->addType(0, 'i18n::ContactForms:Contact form', true);

            $objectType = $objectTypesCollection->getType($contactFormObjectTypeId);

            $objectType->setGuid($guid)->setIsGuidable(true)->save();
        }
        
        $sqlRes = $db->query('select id from ' . DB_PREF . 'page_content_types
            where module = ? and method = ?', array('ContactForms', 'SingleForm'))->toArray();
        
        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $guid = 'contact-form-content-type';
            
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($guid))) {
                $id = $objectTypesCollection->addType(0, 'i18n::ContactForms:Contact form content type', true);

                $objectType = $objectTypesCollection->getType($id);

                $objectType->setGuid($guid)->setPageContentTypeId($contentTypeId)->save();
                
                $groupId = $objectType->addFieldsGroup('common', 'i18n::ContactForms:Common fields group');
                
                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('select');
                
                $fieldsGroup = new FieldsGroup(array(
                    'serviceManager' => $sm,
                    'id' => $groupId,
                ));
                
                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'form_id',
                    'title' => 'i18n::ContactForms:Form id',
                    'field_type_id' => $fieldTypeId,
                    'tip' => '',
                    'is_locked' => 0,
                    'is_visible' => 1,
                    'is_required' => 1,
                    'in_filter' => 0,
                    'in_search' => 0,
                    'guide_id' => $contactFormObjectTypeId,
                ));

                $fieldsGroup->attachField($fieldId);
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
            
            
        }
        
        
    }
    
    public function onUninstall($sm)
    {
        $db = $sm->get('db');
        
        $db->query("DROP TABLE IF EXISTS `" . DB_PREF . "contact_forms`", array());
    }
}