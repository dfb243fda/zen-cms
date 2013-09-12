<?php

namespace ImageGallery;

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

        $guid = 'image-gallery';
        if (null === ($id = $objectTypesCollection->getTypeByGuid($guid))) {  
            $id = $objectTypesCollection->addType(0, 'i18n::ImageGallery:Gallery object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($guid)->setIsGuidable(true)->save();
        }
        $imageGalleryObjectTypeId = $id;
        
        $guid = 'image';
        if (null === $objectTypesCollection->getTypeByGuid($guid)) {  
            $id = $objectTypesCollection->addType(0, 'i18n::ImageGallery:Image object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($guid)->save();
            
            $groupId = $objectType->addFieldsGroup('image-description', 'i18n::ImageGallery:Image description fields group');
                
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('textarea');

            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $sm,
                'id' => $groupId,
            ));

            $fieldId = $fieldsCollection->addField(array(
                'name' => 'image_alt',
                'title' => 'i18n::ImageGallery:Image alt',
                'field_type_id' => $fieldTypeId,
            ));
            
            $fieldsGroup->attachField($fieldId);
            
            
            $groupId = $objectType->addFieldsGroup('image-src', 'i18n::ImageGallery:Image source fields group');
                
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('image');

            $fieldsGroup = new FieldsGroup(array(
                'serviceManager' => $sm,
                'id' => $groupId,
            ));

            $fieldId = $fieldsCollection->addField(array(
                'name' => 'image_src',
                'title' => 'i18n::ImageGallery:Image upload',
                'field_type_id' => $fieldTypeId,
                'is_required' => 1,
            ));
            
            $fieldsGroup->attachField($fieldId);
        }
        
        $db = $sm->get('db');
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('ImageGallery', 'FeGallery'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $guid = 'image-gallery-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($guid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::ImageGallery:Image gallery content object type');
                $objectType = $objectTypesCollection->getType($id);

                $objectType->setPageContentTypeId($contentTypeId);
                    
                $objectType->setGuid($guid)->save();


                $groupId = $objectType->addFieldsGroup('item-info', 'i18n::ImageGallery:Item info fields group');

                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('select');

                $fieldsGroup = new FieldsGroup(array(
                    'serviceManager' => $sm,
                    'id' => $groupId,
                ));

                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'image_gallery_id',
                    'title' => 'i18n::ImageGallery:Image gallery id',
                    'field_type_id' => $fieldTypeId,
                    'is_required' => 1,
                ));

                $fieldsGroup->attachField($fieldId);
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }  
        
        
    }
}