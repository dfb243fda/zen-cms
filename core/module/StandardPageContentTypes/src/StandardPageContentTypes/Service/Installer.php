<?php

namespace StandardPageContentTypes\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Класс для отрисовки дерева страниц в админке
 */
class Installer implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function install()
    {
        $sm = $this->serviceManager;
        $db = $sm->get('db');
        
        $sqlRes = $db->query('select id from ' . DB_PREF . 'page_content_types
            where module = ? and method = ?', array('StandardPageContentTypes', 'SimpleText'))->toArray();
        
        if (!empty($sqlRes)) {
            $pageContentTypeId = $sqlRes[0]['id'];
            
            $objectTypesCollection = $sm->get('objectTypesCollection');
            $fieldsCollection = $sm->get('fieldsCollection');
            $fieldTypesCollection = $sm->get('fieldTypesCollection');
            
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid('simple-text-page-content-type'))) {                
                $id = $objectTypesCollection->addType(0, 'i18n::Simple text object type');
                $objectType = $objectTypesCollection->getType($id);
                $objectType->setGuid('simple-text-page-content-type')->setPageContentTypeId($pageContentTypeId)->save();
                                
                $groupId = $objectType->addFieldsGroup('content', 'i18n::Simple text content fields group');
                
                $fieldsGroup = $objectType->getFieldsGroup($groupId);
                                
                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('ckEditor');
                
                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'main_content',
                    'title' => 'i18n::Simple text content field',
                    'field_type_id' => $fieldTypeId,
                    'tip' => '',
                    'is_locked' => 0,
                    'is_visible' => 1,
                    'is_required' => 0,
                    'in_filter' => 0,
                    'in_search' => 1,
                    'guide_id' => 0,
                ));

                $fieldsGroup->attachField($fieldId);
                
                
                /* comments field */
                $groupId = $objectType->addFieldsGroup('common', 'i18n::Common fields group');

                $fieldsGroup = $objectType->getFieldsGroup($groupId);

                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('checkbox');

                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'allow_comments',
                    'title' => 'i18n::Comments:Allow comments',
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
            
                
            } else {
                $objectType->setPageContentTypeId($pageContentTypeId)->save();
            }
        }
    }
    
}