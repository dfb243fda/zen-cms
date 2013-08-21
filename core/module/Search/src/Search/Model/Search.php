<?php

namespace Search\Model;

use App\Utility\StringUtility;

class Search
{
    protected $serviceManager;
    
    const EQUAL_CLAUSE = 0;
    const LIKE_CLAUSE  = 1;
    
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        
        $this->db = $sm->get('db');
        $this->objectTypesCollection = $sm->get('objectTypesCollection');
        $this->objectPropertyCollection = $sm->get('objectPropertyCollection');
        $this->objectsCollection = $sm->get('objectsCollection');
        
        $configManager = $sm->get('configManager');
        
        $this->itemsOnPage = $configManager->get('search', 'items_on_page');
    }
    
    public function getIndexedPagesCount()
    {
        $sqlRes = $this->db->query('SELECT count(*) as cnt FROM (select distinct url_query from ' . DB_PREF . 'search_index) as distinct_table', array())->toArray();
        
        return $sqlRes[0]['cnt'];
    }
    
    public function refreshIndex()
    {
        $this->db->query('TRUNCATE TABLE ' . DB_PREF . 'search_index', array());
        
        $searchObjectTypes = $this->db->query('
            select guid, object_type_id from ' . DB_PREF . 'search_object_types
        ', array())->toArray();
        
        $items = array();
        
        $config = $this->serviceManager->get('config');
        
        foreach ($searchObjectTypes as $row) {
            $objectTypeId = $row['object_type_id'];
            $guid = $row['guid'];
            
            $sqlRes = $this->db->query('
                select id, created_time from ' . DB_PREF . 'objects
                where type_id = ? 
                    and is_active = 1 
                    and is_deleted = 0 ORDER BY created_time DESC', array($objectTypeId))->toArray();
                
            if (!empty($sqlRes)) {
                $objectType = $this->objectTypesCollection->getType($objectTypeId);
                
                $fieldGroups = $objectType->getFieldGroups();
                
                foreach ($sqlRes as $row2) {
                    $objectId = $row2['id'];
                    
                    $createdTime = $row2['created_time'];

                    $object = $this->objectsCollection->getObject($objectId);
                    
                    $urlQuery = null;

                    if (isset($config['search_url_query'][$guid])) {                        
                        if (is_string($config['search_url_query'][$guid])) {
                            $urlQuery = $config['search_url_query'][$guid];
                        } elseif (is_callable($config['search_url_query'][$guid])) {
                            $urlQuery = call_user_func_array($config['search_url_query'][$guid], array(
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

                                $sqlRes2 = $this->db->query('
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
                                    
                                    $this->db->query('
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

                        $this->db->query('
                            insert into ' . DB_PREF . 'search_index
                                (object_id, guid, object_name, url_query, access, created_time)
                            values (?, ?, ?, ?, ?, ?)    
                            ', $insertData);
                    }
                }
            }            
        }
    }
    
    public function find($searchQuery, $pageNum)
    {
        $availableValueTypes = array(
            'int' => self::EQUAL_CLAUSE,
            'varchar' => self::LIKE_CLAUSE, 
            'text' => self::LIKE_CLAUSE, 
            'float' => self::EQUAL_CLAUSE,
            'object_rel' => self::EQUAL_CLAUSE,
            'page_rel' => self::EQUAL_CLAUSE,
        );
        
        $whereClause = array();
        $bindVars = array();
        
        foreach ($searchQuery as $k=>$value) {
            $tmp = array();
            
            if (substr($k, 0, 9) == 'field_id_') {
                if (is_array($value)) {
                    $fieldId = (int)substr($k, 9);
                    $tmp[] = 'field_id = ?'; 
                    $bindVars[] = $fieldId;

                    foreach ($value as $k2 => $value2) {                        
                        if (isset($availableValueTypes[$k2])) {
                            if ($availableValueTypes[$k2] == self::EQUAL_CLAUSE) {
                                if (is_array($value2)) {
                                    if (isset($value2['min']) || isset($value2['max'])) {
                                        if (isset($value2['min'])) {
                                            $min = (string)$value2['min'];
                                            if (is_numeric($value2)) {
                                                $tmp[] = $k2 . '_val >= ?'; 
                                                $bindVars[] = $min;
                                            }
                                        }
                                        if (isset($value2['min'])) {
                                            $max = (string)$value2['max'];
                                            if (is_numeric($value2)) {
                                                $tmp[] = $k2 . '_val <= ?'; 
                                                $bindVars[] = $max;
                                            }
                                        }
                                    }
                                } else {
                                    $value2 = (string)$value2;
                                    if (is_numeric($value2)) {
                                        $tmp[] = $k2 . '_val = ?'; 
                                        $bindVars[] = $value2;
                                    }
                                }
                            } elseif ($availableValueTypes[$k2] == self::LIKE_CLAUSE) {
                                $value2 = (string)$value2;
                                if ($value2 != '') {
                                    $tmp[] = $k2 . '_val LIKE ?'; 
                                    $bindVars[] = '%' . $value2 . '%';
                                }                                
                            }
                        }
                    }
                }
            } elseif (substr($k, 0, 11) == 'field_name_') {
                if (is_array($value)) {
                    $fieldName = substr($k, 11);
                    $tmp[] = 'field_name = ?';   
                    $bindVars[] = $fieldName;
                    
                    foreach ($value as $k2 => $value2) {
                        if (isset($availableValueTypes[$k2])) {
                            if ($availableValueTypes[$k2] == self::EQUAL_CLAUSE) {
                                if (is_array($value2)) {
                                    if (isset($value2['min']) || isset($value2['max'])) {
                                        if (isset($value2['min'])) {
                                            $min = (string)$value2['min'];
                                            if (is_numeric($value2)) {
                                                $tmp[] = $k2 . '_val >= ?'; 
                                                $bindVars[] = $min;
                                            }
                                        }
                                        if (isset($value2['min'])) {
                                            $max = (string)$value2['max'];
                                            if (is_numeric($value2)) {
                                                $tmp[] = $k2 . '_val <= ?'; 
                                                $bindVars[] = $max;
                                            }
                                        }
                                    }
                                } else {
                                    $value2 = (string)$value2;
                                    if (is_numeric($value2)) {
                                        $tmp[] = $k2 . '_val = ?'; 
                                        $bindVars[] = $value2;
                                    }
                                }
                                
                                
                            } elseif ($availableValueTypes[$k2] == self::LIKE_CLAUSE) {
                                $value2 = (string)$value2;
                                if ($value2 != '') {
                                    $tmp[] = $k2 . '_val LIKE ?'; 
                                    $bindVars[] = '%' . $value2 . '%';
                                }                                
                            }
                        }
                    }
                }
                
            } elseif ($k == 'name') {
                $value = (string)$value;
                
                if ($value != '') {
                    $tmp[] = 'object_name LIKE ?';   
                    $bindVars[] = '%' . $value . '%';
                }                
            } elseif ($k == 'all') {
                $value = (string)$value;               
                
                $tmp2 = array();
                
                if ($value != '') {
                    $tmp2[] = 'object_name LIKE ?';   
                    $bindVars[] = '%' . $value . '%';
                }
                
                $tmp3 = $availableValueTypes;
                unset($tmp3['object_rel']);
                unset($tmp3['page_rel']);
                foreach ($tmp3 as $k2=>$value2) {
                    if ($value2 == self::EQUAL_CLAUSE) {
                        if (is_numeric($value)) {
                            $tmp2[] = $k2 . '_val = ?'; 
                            $bindVars[] = $value;
                        }                        
                    } elseif ($value2 == self::LIKE_CLAUSE) {
                        if ($value != '') {
                            $tmp2[] = $k2 . '_val LIKE ?'; 
                            $bindVars[] = '%' . $value . '%';
                        }                        
                    }
                }
                
                if (!empty($tmp2)) {
                    $tmp[] = '(' . implode(' OR ', $tmp2) . ')';
                }                
            }
            
            if (!empty($tmp)) {
                $whereClause[] = '(' . implode(' AND ', $tmp) . ')';
            }
        }
        
        if (empty($whereClause)) {
            $sqlRes = array();
            $totalCount = 0;
        } else {
            $limit = ($pageNum - 1) * $this->itemsOnPage . ', ' . $this->itemsOnPage;        
            $query = 'select object_id, url_query from ' . DB_PREF . 'search_index where ' . implode(' AND ', $whereClause) . ' ORDER BY created_time DESC LIMIT ' . $limit;
            $sqlRes = $this->db->query($query, $bindVars)->toArray();
 
            $totalCountQuery = 'select count(*) as cnt from (select distinct url_query from ' . DB_PREF . 'search_index where ' . implode(' AND ', $whereClause) . ') as distinct_table';
            $sqlRes2 = $this->db->query($totalCountQuery, $bindVars)->toArray();
            $totalCount = $sqlRes2[0]['cnt'];
        }
        
        $config = $this->serviceManager->get('config');
        
        $items = array();
        
        foreach ($sqlRes as $row) {
            $objectId = $row['object_id'];
            $urlQuery = $row['url_query'];
            
            $object = $this->objectsCollection->getObject($objectId);
            
            if ($object->isExists()) {
                $objectText = '';
                
                $objectType = $this->objectTypesCollection->getType($object->getTypeId());
                
                $fieldGroups = $objectType->getFieldGroups();
                
                foreach ($fieldGroups as $fieldGroup) {
                    $fields = $fieldGroup->getFields();

                    foreach ($fields as $field) {
                        if ($field->getInSearch()) {
                            $fieldId = $field->getId();

                            $property = $this->objectPropertyCollection->getProperty($objectId, $fieldId);

                            $values = (array)$property->getValue();

                            foreach ($values as $value) {
                                if ($field->getFieldTypeName() == 'textarea' || $field->getFieldTypeName() == 'ckEditor') {
                                    if ($objectText == '' && $value != '') {
                                        $objectText .= StringUtility::preText($value, 100);
                                    }
                                }
                            }
                        }
                    }
                }

                $items[$urlQuery] = array(
                    'title' => $object->getName(),
                    'body' => $objectText,
                    'link' => ROOT_URL_SEGMENT . $urlQuery,
                );                
            }
        }
        
                     
        $displayCount = count($items);
        
        $result = array(
            'totalCount' => $totalCount,
            'displayCount' => $displayCount,
            'pageNum' => $pageNum,
            'itemsOnPage' => $this->itemsOnPage,
            'items' => $items,
        );
        
        return $result;
    }
}