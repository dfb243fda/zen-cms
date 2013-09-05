<?php

namespace Search\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use App\Utility\StringUtility;

/**
 * Installs Pages module
 */
class SearchEngine implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    const EQUAL_CLAUSE = 0;
    const LIKE_CLAUSE  = 1;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function find($searchQuery, $pageNum)
    {
        $db = $this->serviceManager->get('db');
        $configManager = $this->serviceManager->get('configManager');
        $itemsOnPage = $configManager->get('search', 'items_on_page');        
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        
        
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
            $limit = ($pageNum - 1) * $itemsOnPage . ', ' . $itemsOnPage;        
            $query = '
                select object_id, url_query 
                from ' . DB_PREF . 'search_index
                where ' . implode(' AND ', $whereClause) . ' 
                ORDER BY created_time 
                DESC LIMIT ' . $limit;
            $sqlRes = $db->query($query, $bindVars)->toArray();
 
            $totalCountQuery = '
                select count(*) as cnt 
                from 
                    (
                        select distinct url_query 
                        from ' . DB_PREF . 'search_index 
                        where ' . implode(' AND ', $whereClause) . '
                    ) as distinct_table';
            $sqlRes2 = $db->query($totalCountQuery, $bindVars)->toArray();
            $totalCount = $sqlRes2[0]['cnt'];
        }
        
        $config = $this->serviceManager->get('config');
        
        $items = array();
        
        foreach ($sqlRes as $row) {
            $objectId = $row['object_id'];
            $urlQuery = $row['url_query'];
            
            $object = $objectsCollection->getObject($objectId);
            
            if ($object->isExists()) {
                $objectText = '';
                
                $objectType = $objectTypesCollection->getType($object->getTypeId());
                
                $fieldGroups = $objectType->getFieldGroups();
                
                foreach ($fieldGroups as $fieldGroup) {
                    $fields = $fieldGroup->getFields();

                    foreach ($fields as $field) {
                        if ($field->getInSearch()) {
                            $fieldId = $field->getId();

                            $property = $objectPropertyCollection->getProperty($objectId, $fieldId);

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
            'itemsOnPage' => $itemsOnPage,
            'items' => $items,
        );
        
        return $result;
    }
}