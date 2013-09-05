<?php

namespace StandardPageTypes\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use App\FieldsGroup\FieldsGroup;

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
        
        $objectTypesCollection = $sm->get('objectTypesCollection');
        $fieldsCollection = $sm->get('fieldsCollection');
        $fieldTypesCollection = $sm->get('fieldTypesCollection');
        
        $sqlRes = $db->query('select id from ' . DB_PREF . 'page_types
            where module = ? and method = ?', array('StandardPageTypes', 'StandardPage'))->toArray();
        
        if (!empty($sqlRes)) {
            $pageTypeId = $sqlRes[0]['id'];
            
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid('standard-page-type'))) {
                $id = $objectTypesCollection->addType(0, 'i18n::Standard page object type');
                $objectType = $objectTypesCollection->getType($id);
                $objectType->setGuid('standard-page-type')->setPageTypeId($pageTypeId)->save();
            } else {
                $objectType->setPageTypeId($pageTypeId)->save();
            }
        }
        
        
        $sqlRes = $db->query('select id from ' . DB_PREF . 'page_types
            where module = ? and method = ?', array('StandardPageTypes', 'PageLink'))->toArray();
        
        if (!empty($sqlRes)) {
            $pageTypeId = $sqlRes[0]['id'];
            
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid('page-link-type'))) {
                $id = $objectTypesCollection->addType(0, 'i18n::Page link object type');
                $objectType = $objectTypesCollection->getType($id);
                $objectType->setGuid('page-link-type')->setPageTypeId($pageTypeId)->save();
                
                $groupId = $objectType->addFieldsGroup('page-link', 'i18n::StandardPageTypes:Page link fields group');
                
                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('text');

                $fieldsGroup = new FieldsGroup(array(
                    'serviceManager' => $sm,
                    'id' => $groupId,
                ));

                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'url',
                    'title' => 'i18n::StandardPageTypes:Page url',
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
            } else {
                $objectType->setPageTypeId($pageTypeId)->save();
            }
        }
    }
    
}