<?php

namespace Search\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Installs Pages module
 */
class Installer implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function install()
    {
        $sm = $this->serviceManager;
        
        $objectTypesCollection = $sm->get('objectTypesCollection');
        
        $db = $sm->get('db');
        
        $configManager = $sm->get('configManager');
        $configManager->set('search', 'items_per_page', 10);
        
        $fieldsCollection = $sm->get('fieldsCollection');
        $fieldTypesCollection = $sm->get('fieldTypesCollection');       
        
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('Search', 'SearchForm'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $guid = 'search-form-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($guid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::Search:SearchForm content object type');
                $objectType = $objectTypesCollection->getType($id);


                $objectType->setPageContentTypeId($contentTypeId)->setGuid($guid)->save();


                $groupId = $objectType->addFieldsGroup('common', 'i18n::Search:SearchForm info fields group');

                $fieldTypeId = $fieldTypesCollection->getFieldTypeIdByDataType('pageLink');

                $fieldsGroup = $objectType->getFieldsGroup($groupId);

                $fieldId = $fieldsCollection->addField(array(
                    'name' => 'result_page_id',
                    'title' => 'i18n::Search:SearchForm result page',
                    'field_type_id' => $fieldTypeId,
                    'tip' => '',
                    'is_locked' => 0,
                    'is_visible' => 1,
                    'is_required' => 1,
                    'in_filter' => 0,
                    'in_search' => 0,
                ));

                $fieldsGroup->attachField($fieldId);
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }
        
        $sqlRes = $db->query('
            select id from ' . DB_PREF . 'page_content_types 
                where module = ? and method = ?', array('Search', 'SearchResult'))->toArray();

        if (!empty($sqlRes)) {
            $contentTypeId = $sqlRes[0]['id'];
            
            $guid = 'search-result-content';
            if (null === ($objectType = $objectTypesCollection->getTypeByGuid($guid))) {  
                $id = $objectTypesCollection->addType(0, 'i18n::Search:SearchResult content object type');
                $objectType = $objectTypesCollection->getType($id);


                $objectType->setPageContentTypeId($contentTypeId)->setGuid($guid)->save();
            } else {
                $objectType->setPageContentTypeId($contentTypeId)->save();
            }
        }
        
        $searchIndexer = $sm->get('Search\Service\SearchIndexer');        
        $searchIndexer->refreshIndex();
        
        $searchObjectTypes = $sm->get('Search\Service\SearchObjectTypes');        
        $searchObjectTypes->refreshObjectTypes();
    }
}