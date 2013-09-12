<?php

namespace Catalog;

use App\FieldsGroup\FieldsGroup;

class Module
{
    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function onInstall($sm)
    {
        $objectTypesCollection = $sm->get('objectTypesCollection');
        $fieldsCollection = $sm->get('fieldsCollection');
        $fieldTypesCollection = $sm->get('fieldTypesCollection');

        $catalogGuid = 'catalog';
        if (null === ($id = $objectTypesCollection->getTypeByGuid($catalogGuid))) {  
            $id = $objectTypesCollection->addType(0, 'i18n::Catalog:Catalog object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($catalogGuid)->setIsGuidable(true)->save();
        }
        $catalogObjectTypeId = $id;
        
        $productGuid = 'product';
        if (null === $objectTypesCollection->getTypeByGuid($productGuid)) {  
            $id = $objectTypesCollection->addType(0, 'i18n::Catalog:Product object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($productGuid)->save();
            
            $groupId = $objectType->addFieldsGroup('product-text', 'i18n::Catalog:Product text fields group');
                
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('ckEditor');

            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $sm,
                'id' => $groupId,
            ));

            $fieldId = $fieldsCollection->addField(array(
                'name' => 'preview',
                'title' => 'i18n::Catalog:Product preview',
                'field_type_id' => $fieldTypeId,
                'tip' => '',
                'is_locked' => 0,
                'is_visible' => 1,
                'is_required' => 0,
                'in_filter' => 0,
                'in_search' => 1,
                'guide_id' => false,
            ));
            
            $fieldsGroup->attachField($fieldId);
            
            $fieldId = $fieldsCollection->addField(array(
                'name' => 'full_text',
                'title' => 'i18n::Catalog:Product full text',
                'field_type_id' => $fieldTypeId,
                'tip' => '',
                'is_locked' => 0,
                'is_visible' => 1,
                'is_required' => 1,
                'in_filter' => 0,
                'in_search' => 1,
                'guide_id' => false,
            ));

            $fieldsGroup->attachField($fieldId);
            
            $groupId = $objectType->addFieldsGroup('publish-info', 'i18n::Catalog:Publish info fields group');
            
            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $sm,
                'id' => $groupId,
            ));
            
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('dateTimePicker');
            
            $fieldId = $fieldsCollection->addField(array(
                'name' => 'publish_date',
                'title' => 'i18n::Catalog:Product publish date',
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
                where module = ? and method = ?', array('Catalog', 'FeProductList'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $productListContentGuid = 'product-list-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($productListContentGuid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::Catalog:Product list content object type');
                $objectType = $objectTypesCollection->getType($id);

                $objectType->setPageContentTypeId($contentTypeId);

                $objectType->setGuid($productListContentGuid)->save();


                $groupId = $objectType->addFieldsGroup('item-info', 'i18n::Catalog:Item info fields group');

                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('select');

                $fieldsGroup = new FieldsGroup(array(
                    'serviceManager' => $sm,
                    'id' => $groupId,
                ));

                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'product_cat_id',
                    'title' => 'i18n::Catalog:Product category id',
                    'field_type_id' => $fieldTypeId,
                    'tip' => '',
                    'is_locked' => 0,
                    'is_visible' => 1,
                    'is_required' => 1,
                    'in_filter' => 0,
                    'in_search' => 0,
                    'guide_id' => $catalogObjectTypeId,
                ));

                $fieldsGroup->attachField($fieldId);
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }
        
        
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('Catalog', 'FeProductItem'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $productListContentGuid = 'product-item-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($productListContentGuid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::Catalog:Product item content object type');
                $objectType = $objectTypesCollection->getType($id);

                $objectType->setPageContentTypeId($contentTypeId);

                $objectType->setGuid($productListContentGuid)->save();
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }
    }
}