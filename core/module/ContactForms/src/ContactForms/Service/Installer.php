<?php

namespace ContactForms\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use App\FieldsGroup\FieldsGroup;

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
        $db = $this->serviceManager->get('db');
        
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $fieldsCollection = $this->serviceManager->get('fieldsCollection');
        $fieldTypesCollection = $this->serviceManager->get('fieldTypesCollection');

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
                
                $fieldsGroup = $objectType->getFieldsGroup($groupId);
                
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
}