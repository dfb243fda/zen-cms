<?php

namespace News;

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

        $newsGuid = 'news-category';
        if (null === ($id = $objectTypesCollection->getTypeByGuid($newsGuid))) {  
            $id = $objectTypesCollection->addType(0, 'i18n::News:News category object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($newsGuid)->setIsGuidable(true)->save();
        }
        $newsCatObjectTypeId = $id;
        
        $newsItemGuid = 'news-item';
        if (null === $objectTypesCollection->getTypeByGuid($newsItemGuid)) {  
            $id = $objectTypesCollection->addType(0, 'i18n::News:News item object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($newsItemGuid)->save();
            
            $groupId = $objectType->addFieldsGroup('news-text', 'i18n::News:Item text fields group');
                
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('ckEditor');

            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $sm,
                'id' => $groupId,
            ));

            $fieldId = $fieldsCollection->addField(array(
                'name' => 'preview',
                'title' => 'i18n::News:News preview',
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
                'title' => 'i18n::News:News full text',
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
            
            $groupId = $objectType->addFieldsGroup('publish-info', 'i18n::News:Publish info fields group');
            
            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $sm,
                'id' => $groupId,
            ));
            
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('dateTimePicker');
            
            $fieldId = $fieldsCollection->addField(array(
                'name' => 'publish_date',
                'title' => 'i18n::News:News publish date',
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
                where module = ? and method = ?', array('News', 'FeNewsList'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $newsItemGuid = 'news-list-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($newsItemGuid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::News:News list content object type');
                $objectType = $objectTypesCollection->getType($id);


                $objectType->setPageContentTypeId($contentTypeId)->setGuid($newsItemGuid)->save();


                $groupId = $objectType->addFieldsGroup('item-info', 'i18n::News:Item info fields group');

                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('select');

                $fieldsGroup = new FieldsGroup(array(
                    'serviceManager' => $sm,
                    'id' => $groupId,
                ));

                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'news_cat_id',
                    'title' => 'i18n::News:News category id',
                    'field_type_id' => $fieldTypeId,
                    'tip' => '',
                    'is_locked' => 0,
                    'is_visible' => 1,
                    'is_required' => 1,
                    'in_filter' => 0,
                    'in_search' => 0,
                    'guide_id' => $newsCatObjectTypeId,
                ));

                $fieldsGroup->attachField($fieldId);
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }
        
        
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('News', 'FeNewsItem'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $newsItemGuid = 'news-item-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($newsItemGuid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::News:News item content object type');
                $objectType = $objectTypesCollection->getType($id);


                $objectType->setPageContentTypeId($contentTypeId)->setGuid($newsItemGuid)->save();
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }
        
        
    }    
}