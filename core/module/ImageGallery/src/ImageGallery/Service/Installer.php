<?php

namespace ImageGallery\Service;

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

        $imageGalleryGuid = 'image-gallery';
        if (null === ($id = $objectTypesCollection->getTypeIdByGuid($imageGalleryGuid))) {  
            $id = $objectTypesCollection->addType(0, 'i18n::ImageGallery:Gallery object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($imageGalleryGuid)->setIsGuidable(true)->save();
        }
        $imageGalleryObjectTypeId = $id;
        
        $imageGuid = 'image';
        if (null === $objectTypesCollection->getTypeByGuid($imageGuid)) {  
            $id = $objectTypesCollection->addType(0, 'i18n::ImageGallery:Image object type');
            $objectType = $objectTypesCollection->getType($id);
            $objectType->setGuid($imageGuid)->save();
            
            $groupId = $objectType->addFieldsGroup('image-description', 'i18n::ImageGallery:Image description fields group');
                
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('textarea');

            $fieldsGroup = $objectType->getFieldsGroup($groupId);

            $fieldId = $fieldsCollection->addField(array(
                'name' => 'image_alt',
                'title' => 'i18n::ImageGallery:Image alt',
                'field_type_id' => $fieldTypeId,
                'tip' => '',
                'is_locked' => 0,
                'is_visible' => 1,
                'is_required' => 0,
                'in_filter' => 0,
                'in_search' => 0,
                'guide_id' => false,
            ));
            
            $fieldsGroup->attachField($fieldId);
            
            
            $groupId = $objectType->addFieldsGroup('common', 'i18n::ImageGallery:Common fields group');
                
            $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('image');

            $fieldsGroup = $objectType->getFieldsGroup($groupId);

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
                where module = ? and method = ?', array('ImageGallery', 'FeImageGallery'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $imageGalleryContentGuid = 'image-gallery-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($imageGalleryContentGuid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::ImageGallery:Image gallery content object type');
                $objectType = $objectTypesCollection->getType($id);

                $objectType->setPageContentTypeId($contentTypeId);

                $objectType->setGuid($imageGalleryContentGuid)->save();


                $groupId = $objectType->addFieldsGroup('common', 'i18n::ImageGallery:Common fields group');

                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('select');

                $fieldsGroup = $objectType->getFieldsGroup($groupId);
                
                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'image_gallery_id',
                    'title' => 'i18n::ImageGallery:Image gallery field',
                    'field_type_id' => $fieldTypeId,
                    'is_required' => 1,
                    'guide_id' => $imageGalleryObjectTypeId,
                ));

                $fieldsGroup->attachField($fieldId);
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }
    }
}