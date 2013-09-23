<?php

namespace Search\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;


class SearchIndexer implements ServiceManagerAwareInterface
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
    
    public function getIndexedPagesCount()
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            SELECT count(*) as cnt 
            FROM (
                select distinct url_query 
                from ' . DB_PREF . 'search_index
                    ) as distinct_table', array())->toArray();
        
        return $sqlRes[0]['cnt'];
    }
    
    protected function getSearchUrlQuery()
    {
        $config = $this->serviceManager->get('config');
        
        if (isset($config['search_url_query'])) {
            $searchUrlQuery = $config['search_url_query'];
        } else {
            $searchUrlQuery = array();
        }
        
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        foreach ($moduleManager->getActiveModules() as $moduleKey=>$moduleConfig) {
            $className = $moduleKey . '\Module';
            $instance = new $className;
            
            if (is_callable(array($instance, 'getSearchUrlQuery'))) {
                $tmp = $instance->getSearchUrlQuery($this->serviceManager);
                $searchUrlQuery = array_merge($searchUrlQuery, $tmp);
            }
        }
        
        return $searchUrlQuery;
    }
    
    public function refreshIndex()
    {
        $db = $this->serviceManager->get('db');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        
        $db->query('TRUNCATE TABLE ' . DB_PREF . 'search_index', array());
        
        $searchObjectTypes = $db->query('
            select guid, object_type_id from ' . DB_PREF . 'search_object_types
        ', array())->toArray();
        
        $items = array();
        
        $searchUrlQuery = $this->getSearchUrlQuery();
        
        foreach ($searchObjectTypes as $row) {
            $objectTypeId = $row['object_type_id'];
            $guid = $row['guid'];
            
            $sqlRes = $db->query('
                select id, created_time from ' . DB_PREF . 'objects
                where type_id = ? 
                    and is_active = 1 
                    and is_deleted = 0 ORDER BY created_time DESC', array($objectTypeId))->toArray();
                
            if (!empty($sqlRes)) {
                $objectType = $objectTypesCollection->getType($objectTypeId);
                
                $fieldGroups = $objectType->getFieldGroups();
                
                foreach ($sqlRes as $row2) {
                    $objectId = $row2['id'];
                    
                    $createdTime = $row2['created_time'];

                    $object = $objectsCollection->getObject($objectId);
                    
                    $urlQuery = null;

                    if (isset($searchUrlQuery[$guid])) {
                        if (is_string($searchUrlQuery[$guid])) {
                            $urlQuery = $searchUrlQuery[$guid];
                        } elseif (is_callable($searchUrlQuery[$guid])) {
                            $urlQuery = call_user_func_array($searchUrlQuery[$guid], array(
                                $this->serviceManager,
                                $objectId
                            ));
                        }
                    }
                    
                    if (null === $urlQuery) {
                        continue;
                    }
                    $access = '-2';
                    
                    $hasInSearchFields = false;
                    
                    foreach ($fieldGroups as $fieldGroup) {
                        $fields = $fieldGroup->getFields();

                        foreach ($fields as $field) {
                            if ($field->getInSearch()) {
                                $hasInSearchFields = true;
                                
                                $fieldId = $field->getId();
                                $fieldName = $field->getName();

                                $sqlRes2 = $db->query('
                                    select * from ' . DB_PREF . 'object_content 
                                    where object_id = ? 
                                        and field_id = ?', array($objectId, $fieldId))->toArray();
                                
                                if (!empty($sqlRes2)) {
                                    $contentData = $sqlRes2[0];
                                    
                                    $insertData = array(
                                        $objectId, 
                                        $fieldId, 
                                        $fieldName,
                                        $contentData['int_val'],
                                        $contentData['varchar_val'], 
                                        $contentData['text_val'],
                                        $contentData['float_val'], 
                                        $contentData['object_rel_val'],
                                        $contentData['page_rel_val'],
                                        $guid,
                                        $urlQuery,
                                        $access,
                                        $createdTime,
                                    );
                                    
                                    $db->query('
                                        insert into ' . DB_PREF . 'search_index
                                            (object_id, field_id, field_name, int_val, varchar_val, text_val, float_val, object_rel_val, page_rel_val, guid, url_query, access, created_time)
                                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)    
                                        ', $insertData);
                                }
                            }
                        }
                    }
                    
                    if ($hasInSearchFields) {
                        $insertData = array(
                            $objectId, 
                            $guid,
                            $object->getName(),
                            $urlQuery,
                            $access,
                            $createdTime,
                        );

                        $db->query('
                            insert into ' . DB_PREF . 'search_index
                                (object_id, guid, object_name, url_query, access, created_time)
                            values (?, ?, ?, ?, ?, ?)    
                            ', $insertData);
                    }
                }
            }            
        }
    }
}