<?php

namespace Pages\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;
use App\Utility\GeneralUtility;
use Zend\Http\Response;
use Pages\AbstractMethod\FeContentMethodInterface;

class Page implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $pageTypeId;
    
    protected $pageId;
    
    protected $formFactory;
    
    protected $domainsTable = 'domains';
    
    protected $domainMirrorsTable = 'domain_mirrors';
    
    protected $objectsTable = 'objects';
    
    protected $pagesTable = 'pages';
    
    protected $templatesTable = 'templates';
        
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($typeId)
    {
        $this->objectTypeId = $typeId;
        return $this;
    }
    
    public function setPageTypeId($typeId)
    {
        $this->pageTypeId = $typeId;
        return $this;
    }
    
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
        return $this;
    }
    
    public function getForm()
    {                
        $this->formFactory = $formFactory = $this->serviceManager->get('Pages\FormFactory\Page');
        
        $formFactory->setPageTypeId($this->pageTypeId)
                    ->setObjectTypeId($this->objectTypeId)
                    ->setPageId($this->pageId);
        
        $form = $formFactory->getForm();
        
        return $form;
    }
    
    public function getPageFormData()
    {
        if (null === $this->formFactory) {
            throw new \Exception('form does not created yet, youy can create it with getForm() method');
        }
        
        return $this->formFactory->getPageData();
    }
    
    public function editPage($data)
    {        
        $pageId = $this->pageId;
        
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $db = $this->serviceManager->get('db');
        
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

        $tmp = $this->getPageFormData();
        $objectId = $tmp['object_id'];
        $domainId = $tmp['domain_id'];

        $objectTypeId = $insertBase['object_type_id'];

        $object = $objectsCollection->getObject($objectId);            
        $object->setName($insertBase['name'])->setTypeId($objectTypeId)->save();


        unset($insertBase['name']);
        unset($insertBase['object_type_id']);

        if ($insertBase['is_default']) {
            $db->query('update ' . DB_PREF . $this->pagesTable . ' set is_default = 0 where domain_id = ?',  array($domainId));
        }
        if ($insertBase['is_403']) {
            $db->query('update ' . DB_PREF . $this->pagesTable . ' set is_403 = 0 where domain_id = ?',  array($domainId));
        }
        if ($insertBase['is_404']) {
            $db->query('update ' . DB_PREF . $this->pagesTable . ' set is_404 = 0 where domain_id = ?',  array($domainId));
        }
        
        $sql = new Sql($db);
        
        $update = $sql->update(DB_PREF . $this->pagesTable)->set($insertBase)->where('id = ' . (int)$pageId);
        $sql->prepareStatementForSqlObject($update)->execute();    

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

        return true;
    }
    
    /**
     * Определяет по URL страницу, и возвращает массив с данными о ней
     */
    public function getPageData()
    {
        $request = $this->serviceManager->get('request');
        $db = $this->serviceManager->get('db');
        $configManager = $this->serviceManager->get('configManager');
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        $paramsPlugin = $this->serviceManager->get('ControllerPluginManager')->get('params');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $translator = $this->serviceManager->get('translator');
        
        
        $result = array();
        
        $host = $request->getUri()->getHost() . $request->getBasePath();
        
        $sqlRes = $db->query('select id from ' . DB_PREF . $this->domainsTable . ' where host = ?', array($host))->toArray();
        
        
        if (empty($sqlRes)) {
            $sqlRes = $db->query('
                select t1.rel,
                (select host from ' . DB_PREF . $this->domainsTable . ' t2 where t2.id = t1.rel) as host
                from ' . DB_PREF . $this->domainMirrorsTable . ' t1 where t1.host = ?', array($host))->toArray();
            
            if (empty($sqlRes)) {
                $sqlRes = $db->query('select id, host from ' . DB_PREF . $this->domainsTable . ' where is_default = 1', array())->toArray();
                
                if (empty($sqlRes)) {
                    throw new \Exception('there is no default domain in the system');
                }
                
                $domainId = $sqlRes[0]['id'];
                $canonicalHost = $sqlRes[0]['host'];
            } else {
                $domainId = $sqlRes[0]['rel'];
                $canonicalHost = $sqlRes[0]['host'];
            }
        } else {
            $domainId = $sqlRes[0]['id'];
            $canonicalHost = $host;
        }        
        
        $queryParams = $paramsPlugin->fromQuery();
        ksort($queryParams);
        
        $tmp = $urlPlugin->fromRoute(null, array(), array(
            'query' => $queryParams,
        ));
        $tmp = substr($tmp, strlen(ROOT_URL_SEGMENT));       
        
        $result['canonicalUrl'] = $request->getUri()->getScheme() . '://' . $canonicalHost . $tmp; 
        
        if (null !== $paramsPlugin->fromRoute('pageId')) {
            $pageId = (int)$paramsPlugin->fromRoute('pageId');
            $sqlRes = $b->query('
                select t1.*, t2.name as title
                from ' . DB_PREF . $this->pagesTable . ' t1, ' . DB_PREF . $this->objectsTable . ' t2
                where t1.id = ? and t1.domain_id = ? and t1.object_id = t2.id and t1.is_deleted = 0 and t1.is_active = 1 and t1.is_403 = 0 and t1.is_404 = 0
                limit 1', array($pageId, $domainId))->toArray();
        } elseif (null !== $paramsPlugin->fromRoute('pageAlias')) {
            $replaceSpacesWith = $configManager->get('pages', 'replace_spaces_with');
            
            $pageAlias = (string)$paramsPlugin->fromRoute('pageAlias');
            $sqlRes = $db->query('
                select t1.*, t2.name as title
                from ' . DB_PREF . $this->pagesTable . ' t1, ' . DB_PREF . $this->objectsTable . ' t2
                where (replace(t1.alias, " ", ?) = ? or replace(t2.name, " ", ?) = ?) and t1.domain_id = ? and t1.object_id = t2.id and t1.is_deleted = 0 and t1.is_active = 1 and t1.is_403 = 0 and t1.is_404 = 0
                limit 1', array($replaceSpacesWith, $pageAlias, $replaceSpacesWith, $pageAlias, $domainId))->toArray();
        } else {
            $sqlRes = $db->query('
                select t1.*, t2.name as title
                from ' . DB_PREF . $this->pagesTable . ' t1, ' . DB_PREF . $this->objectsTable . ' t2
                where t1.is_default = 1 and t1.domain_id = ? and t1.object_id = t2.id and t1.is_deleted = 0 and t1.is_active = 1 and t1.is_403 = 0 and t1.is_404 = 0
                limit 1', array($domainId))->toArray();
        }
               
        $result['link'] = $request->getRequestUri();
        $result['statusCode'] = Response::STATUS_CODE_200;
        
        if (empty($sqlRes)) {
            $result['statusCode'] = Response::STATUS_CODE_404;
            
            $sqlRes = $db->query('
                select t1.*, t2.name as title
                from ' . DB_PREF . $this->pagesTable . ' t1, ' . DB_PREF . $this->objectsTable . ' t2
                where t1.is_404 = 1 and t1.domain_id = ? and t1.object_id = t2.id and t1.is_deleted = 0 and t1.is_active = 1
                limit 1', array($domainId))->toArray();

            if (empty($sqlRes)) {
                return $result;
            }
            $pageData = $sqlRes[0];
        } else {
            $pageData = $sqlRes[0];
        }
        
        
        if (!$this->checkAccess($pageData['access'])) {
            
            if ('' != $pageData['non_access_url']) {
                $url = $pageData['non_access_url'];
        
                if (!GeneralUtility::isValidUrl($url)) {
                    $url = ROOT_URL_SEGMENT . $url;
                }
                
                $result['redirectUrl'] = $url;
                
                return $result;
            } else {
                $result['statusCode'] = Response::STATUS_CODE_403;
            
                $sqlRes = $db->query('
                    select t1.*, t2.name as title
                    from ' . DB_PREF . $this->pagesTable . ' t1, ' . DB_PREF . $this->objectsTable . ' t2
                    where t1.is_403 = 1 and t1.domain_id = ? and t1.object_id = t2.id and t1.is_deleted = 0 and t1.is_active = 1
                    limit 1', array($domainId))->toArray();

                if (empty($sqlRes)) {
                    return $result;
                }
                $pageData = $sqlRes[0];
            }            
        }
        
        $result = array_merge($result, $pageData);
        
        if (null !== $paramsPlugin->fromRoute('lang')) {
            $lang = (string)$paramsPlugin->fromRoute('lang');
            $sqlRes = $db->query('select id from ' . DB_PREF . 'langs where prefix = ?', array($lang))->toArray();
            if (!empty($sqlRes)) {
                $result['language'] = $lang;
            }            
        } else {
            $sqlRes = $db->query('select prefix from ' . DB_PREF . 'langs where id = ?', array($result['lang_id']))->toArray();
            if (!empty($sqlRes)) {
                $result['language'] = $sqlRes[0]['prefix'];
            }
        }
        
        
        $object = $objectsCollection->getObject($result['object_id']);            
        $objectTypeId = $object->getTypeId();
        $objectType = $objectTypesCollection->getType($objectTypeId);

        $fieldGroups = $objectType->getFieldGroups();

        $result['fieldGroups'] = array();
        foreach ($fieldGroups as $k=>$fieldsGroup) {
            $groupName = $fieldsGroup->getName();
            $result['fieldGroups'][$groupName] = $fieldsGroup->getGroupData();
            $result['fieldGroups'][$groupName]['title'] = $translator->translateI18n($result['fieldGroups'][$groupName]['title']);
            
            $fields = $fieldsGroup->getFields();
            $result['fieldGroups'][$groupName]['fields'] = array();
            foreach ($fields as $field) {
                $property = $objectPropertyCollection->getProperty($result['object_id'], $field->getId());
                $result['fieldGroups'][$groupName]['fields'][$field->getName()] = $property->getValue();
            }                
        }
        

        $sqlRes = $db->query('select id, name from ' . DB_PREF . 'template_markers where template_id = ?', array($result['template']))->toArray();

        $markers = array();
        foreach ($sqlRes as $row) {
            $markers[$row['id']] = $row['name'];
        }

        $sqlRes = $db->query('select * from ' . DB_PREF . 'page_content_types', array())->toArray();

        $pageContentTypes = array();
        foreach ($sqlRes as $row) {
            $pageContentTypes[$row['id']] = $row;
        }

        $sqlRes = $db->query('
            select *
            from ' . DB_PREF . 'pages_content
            where page_id = ?
                and is_active = 1
                and is_deleted = 0
            order by sorting
        ', array($result['id']))->toArray();

        $content = array();
        foreach ($markers as $markerName) {
            $content[$markerName] = array();
        }
        foreach ($sqlRes as $row) {        
            if (!$this->checkAccess($row['access'])) {
                continue;
            }
            
            if (isset($pageContentTypes[$row['page_content_type_id']])) {
                $object = $objectsCollection->getObject($row['object_id']);            
                $objectTypeId = $object->getTypeId();
                $objectType = $objectTypesCollection->getType($objectTypeId);

                $fieldGroups = $objectType->getFieldGroups();

                $row['fieldGroups'] = array();
                foreach ($fieldGroups as $k=>$fieldsGroup) {
                    $groupName = $fieldsGroup->getName();
                    $row['fieldGroups'][$groupName] = $fieldsGroup->getGroupData();
                    $row['fieldGroups'][$groupName]['title'] = $translator->translateI18n($row['fieldGroups'][$groupName]['title']);

                    $fields = $fieldsGroup->getFields();
                    $row['fieldGroups'][$groupName]['fields'] = array();
                    foreach ($fields as $field) {
                        $property = $objectPropertyCollection->getProperty($row['object_id'], $field->getId());
                        $row['fieldGroups'][$groupName]['fields'][$field->getName()] = $property->getValue();
                    }                
                }

                if (isset($markers[$row['marker']])) {
                    $markerName = $markers[$row['marker']];
                } else {
                    $markerName = $row['marker'];
                    trigger_error('there is no marker ' . $row['marker'] . ' in template ' . $this->_pageData['template']);
                }

                $instance = $this->serviceManager->get('methodManager')->get($pageContentTypes[$row['page_content_type_id']]['service']);

                if (!$instance instanceof FeContentMethodInterface) {
                    throw new \Exception(get_class($instance) . ' does not implements \Pages\AbstractMethod\FeContentMethodInterface');
                }

                $instance->init();

                $instance->setContentData($row);

                $tmpResult = (array)$instance->main();

                $tmpResult = array_merge($row, $tmpResult);

                $content[$markerName][] = $tmpResult;

            } else {
                trigger_error('undefined page content type ' . $row['page_content_type_id'], E_USER_WARNING);
            }
        }
        $result['content'] = $content;
        
        return $result;
    }
    
    public function getTemplate($template)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('
            select *
            from ' . DB_PREF . $this->templatesTable . '
            where id = ?', array($template))->toArray();
        
        if (empty($sqlRes)) {
            throw new \Exception('template ' . $template . ' is not find');
        }
        
        return $sqlRes[0];        
    }
    
    public function checkAccess($access)
    {
        $usersIdentityProvider = $this->serviceManager->get('Rbac\Provider\Identity\UsersZendDb');
        
        $usersAuthService = $this->serviceManager->get('users_auth_service');
        
        $currentRoles = $usersIdentityProvider->getIdentityRoles();
        
        $checkRoles = explode(',', $access);
        
        foreach ($checkRoles as $v) {
            if ($v == '-2') {
                return true;
            } elseif ($v == '-1') {
                if ($usersAuthService->hasIdentity()) {
                    return true;
                }
            } elseif ($v == '0') {
                if (!$usersAuthService->hasIdentity()) {
                    return true;
                }
            } else {
                if (in_array('id_' . $v, $currentRoles)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
}