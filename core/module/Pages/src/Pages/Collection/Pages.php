<?php

namespace Pages\Collection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;

class Pages implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $domainId;
    
    protected $parentPageId;
    
    protected $formFactory;
    
    protected $objectTypeId;
    
    protected $pageTypeId;
    
    protected $domainsTable = 'domains';
    
    protected $pagesTable = 'pages';
    
    protected $pagesContentTable = 'pages_content';
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
        return $this;
    }
    
    public function setParentPageId($parentPageId)
    {
        $this->parentPageId = $parentPageId;
        return $this;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function setPageTypeid($pageTypeId)
    {
        $this->pageTypeId = $pageTypeId;
        return $this;
    }
    
    public function getForm()
    {                
        $this->formFactory = $formFactory = $this->serviceManager->get('Pages\FormFactory\Page');
        
        $formFactory->setPageTypeId($this->pageTypeId)
                    ->setObjectTypeId($this->objectTypeId)
                    ->setParentPageId($this->parentPageId)
                    ->setDomainId($this->domainId);
        
        $form = $formFactory->getForm();
        
        return $form;
    }
    
    public function getPageData()
    {
        return $this->formFactory->getPageData();
    }
    
    public function addPage($data)
    {
        $parentId = $this->parentPageId;
        $domainId = $this->domainId;
        
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $db = $this->serviceManager->get('db');
        
        if (null !== $domainId) {
            $parentId = 0;
            $sqlRes = $db->query('select id from ' . DB_PREF . $this->domainsTable . ' where id = ?', array($domainId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('domain ' . $domainId . ' does not found');
            }            
        } elseif (null !== $parentId) {
            $sqlRes = $db->query('select domain_id from ' . DB_PREF . $this->pagesTable . ' where id = ?', array($parentId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('page ' . $parentId . ' does not found');
            }  
            $domainId = $sqlRes[0]['domain_id'];
        } else {
            throw new \Exception('wrong parameterss transferred');
        }
        
        $insertFields = array(); //значения для свойств объектов (такблица object и т.д.)
        $insertBase = array(); // значения для таблицы pages

        foreach ($data as $groupKey=>$groupData) {
            foreach ($groupData as $fieldName=>$fieldVal) {
                if ('field_' == substr($fieldName, 0, 6)) {
                    $insertFields[substr($fieldName, 6)] = $fieldVal;
                } else {
                    if (is_array($fieldVal)) {
                        $fieldVal = implode(',', $fieldVal);
                    }                        
                    $insertBase[$fieldName] = $fieldVal;
                }
            }
        }

        $objectId = $objectsCollection->addObject($insertBase['name'], $insertBase['object_type_id']);
        $objectTypeId = $insertBase['object_type_id'];

        unset($insertBase['name']);
        unset($insertBase['object_type_id']);
        
        $insertBase['domain_id'] = $domainId;
        $insertBase['parent_id'] = $parentId;
        $insertBase['object_id'] = $objectId;
        $insertBase['is_deleted'] = 0;

        $sqlRes = $db->query('SELECT MAX(sorting) AS max_sorting FROM ' . DB_PREF . 'pages WHERE parent_id = ? AND domain_id = ?', array($parentId, $domainId))->toArray();            
        if (null === $sqlRes[0]['max_sorting']) {
            $insertBase['sorting'] = 0;              
        } else {
            $insertBase['sorting'] = $sqlRes[0]['max_sorting'] + 1;
        }

        if ($insertBase['is_default']) {
            $db->query('update ' . DB_PREF . 'pages set is_default = 0 where domain_id = ?',  array($domainId));
        }
        if ($insertBase['is_403']) {
            $db->query('update ' . DB_PREF . 'pages set is_403 = 0 where domain_id = ?',  array($domainId));
        }
        if ($insertBase['is_404']) {
            $db->query('update ' . DB_PREF . 'pages set is_404 = 0 where domain_id = ?',  array($domainId));
        }
        
        $sql = new Sql($db);
        $insert = $sql->insert(DB_PREF . 'pages')->values($insertBase);
        $sql->prepareStatementForSqlObject($insert)->execute();    

        $pageId = $db->getDriver()->getLastGeneratedValue();

        $objectType = $objectTypesCollection->getType($objectTypeId); 
        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (isset($insertFields[$k2])) {
                    $property = $objectPropertyCollection->getProperty($objectId, $k2);                        
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }

        return $pageId;
    }
        
    public function deletePage($pageId)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select id from ' . DB_PREF . $this->pagesTable . ' where id = ? and is_deleted = 0', array($pageId))->toArray();

        if (empty($sqlRes)) {
            return false;
        } else {
            $db->query('update ' . DB_PREF . $this->pagesTable . ' set is_deleted = 1 where id = ?', array($pageId));

            $sqlRes = $db->query('select id from ' . DB_PREF . $this->pagesTable . ' where parent_id = ? and is_deleted = 0', array($pageId));

            foreach ($sqlRes as $row) {
                $this->deletePage($row['id']);
            }

            $db->query('update ' . DB_PREF . $this->pagesContentTable . ' set is_deleted = 0 where page_id = ?', array($pageId));

            return true;
        }
    }
}