<?php

namespace Search;

use Zend\Mvc\MvcEvent;
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
    
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        
        $eventManager = $locator->get('application')->getEventmanager();   
        $eventManager->attach('module_installed', function($e) use ($locator) {
            $params = $e->getParams();
            
            $module = $params['module'];
            
            $moduleConfig = $e->getTarget()->getModuleConfig($module);
            
            $objectTypesCollection = $locator->get('objectTypesCollection');
            
            $db = $locator->get('db');
            
            if (!empty($moduleConfig['search_object_types'])) {
                foreach ($moduleConfig['search_object_types'] as $value) {
                    if (isset($value['guid'])) {
                        $guid = $value['guid'];

                        $typeId = $objectTypesCollection->getTypeIdByGuid($guid);

                        $typeIds = array();
                        if (null !== $typeId) {
                            $typeIds[] = $typeId;

                            if (isset($value['with_descendants']) && $value['with_descendants']) {
                                $descendantIds = $objectTypesCollection->getDescendantTypeIds($typeId);

                                $typeIds = array_merge($typeIds, $descendantIds);
                            }
                        }

                        if (!empty($typeIds)) {
                            foreach ($typeIds as $id) {
                                $db->query('insert ignore into ' . DB_PREF . 'search_object_types
                                    (guid, object_type_id, module)
                                    values (?, ?, ?)', array($guid, $id, $module));
                            }
                        }
                    }
                }
            }
        });
        
        $eventManager->attach('module_uninstalled', function($e) use ($locator) {
            $params = $e->getParams();
   
            $module = $params['module'];
                        
            $db = $locator->get('db');

            $db->query('delete from ' . DB_PREF . 'search_object_types
                where module = ?', array($module));
        });
    }
    
    public function onInstall($sm)
    {
        $moduleManager = $sm->get('moduleManager');
        
        $modules = $moduleManager->getActiveModules();
        
        $objectTypesCollection = $sm->get('objectTypesCollection');
        
        $db = $sm->get('db');
        
        $configManager = $sm->get('configManager');
        $configManager->set('search', 'items_on_page', 10);
        
        foreach ($modules as $module => $moduleConfig) {
            if (!empty($moduleConfig['search_object_types'])) {
                foreach ($moduleConfig['search_object_types'] as $value) {
                    if (isset($value['guid'])) {
                        $guid = $value['guid'];
                        
                        $typeId = $objectTypesCollection->getTypeIdByGuid($guid);
                        
                        $typeIds = array();
                        if (null !== $typeId) {
                            $typeIds[] = $typeId;
                            
                            if (isset($value['with_descendants']) && $value['with_descendants']) {
                                $descendantIds = $objectTypesCollection->getDescendantTypeIds($typeId);
                                
                                $typeIds = array_merge($typeIds, $descendantIds);
                            }
                        }
                        
                        if (!empty($typeIds)) {
                            foreach ($typeIds as $id) {
                                $db->query('insert ignore into ' . DB_PREF . 'search_object_types
                                    (guid, object_type_id, module)
                                    values (?, ?, ?)', array($guid, $id, $module));
                            }
                        }
                    }
                }
            }
        }
        
        
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

                $fieldsGroup = new FieldsGroup(array(
                    'serviceManager' => $sm,
                    'id' => $groupId,
                ));

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
        
        require_once __DIR__ . '/src/Search/Model/Search.php';
        $searchModel = new \Search\Model\Search($sm);
        
        $searchModel->refreshIndex();
        
    }
    
    public function onUninstall($sm)
    {
        $db = $sm->get('db');
        
        $db->query("DROP TABLE IF EXISTS `" . DB_PREF . "search_object_types`", array());
        $db->query("DROP TABLE IF EXISTS `" . DB_PREF . "search_index`", array());
    }
    
    public function getTablesSql($sm)
    {
        return file_get_contents(__DIR__ . '/tables/tables.sql');
    }
}