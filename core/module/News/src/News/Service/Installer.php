<?php

namespace News\Service;

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

        $rubricGuid = 'news-rubric';
        if (null === ($id = $objectTypesCollection->getTypeIdByGuid($rubricGuid))) {  
            $id = $objectTypesCollection->addType(0, 'i18n::News:Rubric object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($rubricGuid)->setIsGuidable(true)->save();
        }
        $rubricObjectTypeId = $id;
        
        $newsGuid = 'news';
        if (null === $objectTypesCollection->getTypeByGuid($newsGuid)) {  
            $id = $objectTypesCollection->addType(0, 'i18n::News:News object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($newsGuid)->save();
            
            $groupId = $objectType->addFieldsGroup('news-text', 'i18n::News:Item text fields group');
                
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('ckEditor');

            $fieldsGroup = $objectType->getFieldsGroup($groupId);

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
            
            $fieldsGroup = $objectType->getFieldsGroup($groupId);
            
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
            
            $newsListContentGuid = 'news-list-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($newsListContentGuid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::News:News list content object type');
                $objectType = $objectTypesCollection->getType($id);

                $objectType->setPageContentTypeId($contentTypeId);

                $objectType->setGuid($newsListContentGuid)->save();


                $groupId = $objectType->addFieldsGroup('item-info', 'i18n::News:Item info fields group');

                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('select');

                $fieldsGroup = $objectType->getFieldsGroup($groupId);

                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'news_rubric_id',
                    'title' => 'i18n::News:News rubric id',
                    'field_type_id' => $fieldTypeId,
                    'tip' => '',
                    'is_locked' => 0,
                    'is_visible' => 1,
                    'is_required' => 1,
                    'in_filter' => 0,
                    'in_search' => 0,
                    'guide_id' => $rubricObjectTypeId,
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
            
            $newsItemContentGuid = 'news-item-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($newsItemContentGuid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::News:News item content object type');
                $objectType = $objectTypesCollection->getType($id);

                $objectType->setPageContentTypeId($contentTypeId);

                $objectType->setGuid($newsItemContentGuid)->save();
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }
        
    }
}