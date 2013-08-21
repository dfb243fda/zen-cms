<?php

namespace Pages\Service;

use Zend\Http\Response;
use Pages\Entity\FeContentMethodInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

use App\Utility\GeneralUtility;

class Page implements ServiceManagerAwareInterface
{
    protected $initialized = false;
    
    protected $serviceManager;
    
    protected $request;
    
    protected $pageData;
    
    protected $domainsTable = 'domains';
    
    protected $domainMirrorsTable = 'domain_mirrors';
    
    protected $pagesTable = 'pages';
    
    protected $objectsTable = 'objects';
    
    protected $templatesTable = 'templates';
    
    protected $usersIdentityProvider;
    
    protected $usersAuthService;
    
    protected $configManager;
        
    protected $translator;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    protected function init()
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;
        
        $sm = $this->serviceManager;
        
        $this->request = $sm->get('request');
        
        $this->params = $sm->get('ControllerPluginManager')->get('params');
        
        $this->db = $sm->get('db');
        
        $this->objectsCollection = $sm->get('objectsCollection');
        
        $this->objectTypesCollection = $sm->get('objectTypesCollection');
        
        $this->objectPropertyCollection = $sm->get('objectPropertyCollection');
        
        $this->usersIdentityProvider = $sm->get('Rbac\Provider\Identity\UsersZendDb');
        
        $this->usersAuthService = $sm->get('users_auth_service');
        
        $this->configManager = $sm->get('configManager');
        
        $this->translator = $sm->get('translator');
    }
    
    public function getPageUrlParams($pageId)
    {
        $this->init();
        
        $sqlRes = $this->db->query('select t1.alias, (select t2.name from ' . DB_PREF . 'objects t2 where t2.id = t1.object_id) as title from ' . DB_PREF . 'pages t1 where t1.id = ?', array($pageId))->toArray();
        
        if (empty($sqlRes)) {
            return null;
        }
        
        if ($sqlRes[0]['alias'] == '') {
            $pageAlias = $sqlRes[0]['title'];
        } else {
            $pageAlias = $sqlRes[0]['alias'];
        }
        $replaceSpacesWith = $this->configManager->get('pages', 'replace_spaces_with');
        $pageAlias = str_replace(' ', $replaceSpacesWith, $pageAlias);
        
        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        return array(
            'pageId' => $pageId,
            'pageAlias' => $pageAlias,
        );
    }
    
    public function getPageUrl($pageId)
    {
        $urlParams = $this->getPageUrlParams($pageId);
        
        if (null === $urlParams) {
            return null;
        }        
        
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        
        $url = $urlPlugin->fromRoute('fe', $urlParams);
        
        return $url;
    }
    
    public function getPageData()
    {
        $this->init();
        
        $result = array();
        
        $host = $this->request->getUri()->getHost() . $this->request->getBasePath();
        
        $sqlRes = $this->db->query('select id from ' . DB_PREF . $this->domainsTable . ' where host = ?', array($host))->toArray();
        
        if (empty($sqlRes)) {
            $sqlRes = $this->db->query('
                select t1.rel,
                (select host from ' . DB_PREF . $this->domainsTable . ' t2 where t2.id = t1.rel) as host
                from ' . DB_PREF . $this->domainMirrorsTable . ' t1 where t1.host = ?', array($host))->toArray();
            
            if (empty($sqlRes)) {
                $sqlRes = $this->db->query('select id, host from ' . DB_PREF . $this->domainsTable . ' where is_default = 1', array())->toArray();
                
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
                
        $urlPlugin = $this->serviceManager->get('ControllerPluginManager')->get('url');
        $paramsPlugin = $this->serviceManager->get('ControllerPluginManager')->get('params');
        
        $queryParams = $paramsPlugin->fromQuery();
        ksort($queryParams);
        
        $tmp = $urlPlugin->fromRoute(null, array(), array(
            'query' => $queryParams,
        ));
        $tmp = substr($tmp, strlen(ROOT_URL_SEGMENT));       
        
        $result['canonicalUrl'] = $this->request->getUri()->getScheme() . '://' . $canonicalHost . $tmp; 
            
        if (null !== $this->params->fromRoute('pageId')) {
            $pageId = (int)$this->params->fromRoute('pageId');
            $sqlRes = $this->db->query('
                select t1.*, t2.name as title
                from ' . DB_PREF . $this->pagesTable . ' t1, ' . DB_PREF . $this->objectsTable . ' t2
                where t1.id = ? and t1.domain_id = ? and t1.object_id = t2.id and t1.is_deleted = 0 and t1.is_active = 1 and t1.is_403 = 0 and t1.is_404 = 0
                limit 1', array($pageId, $domainId))->toArray();
        } elseif (null !== $this->params->fromRoute('pageAlias')) {
            $replaceSpacesWith = $this->configManager->get('pages', 'replace_spaces_with');
            
            $pageAlias = (string)$this->params->fromRoute('pageAlias');
            $sqlRes = $this->db->query('
                select t1.*, t2.name as title
                from ' . DB_PREF . $this->pagesTable . ' t1, ' . DB_PREF . $this->objectsTable . ' t2
                where (replace(t1.alias, " ", ?) = ? or replace(t2.name, " ", ?) = ?) and t1.domain_id = ? and t1.object_id = t2.id and t1.is_deleted = 0 and t1.is_active = 1 and t1.is_403 = 0 and t1.is_404 = 0
                limit 1', array($replaceSpacesWith, $pageAlias, $replaceSpacesWith, $pageAlias, $domainId))->toArray();
        } else {
            $sqlRes = $this->db->query('
                select t1.*, t2.name as title
                from ' . DB_PREF . $this->pagesTable . ' t1, ' . DB_PREF . $this->objectsTable . ' t2
                where t1.is_default = 1 and t1.domain_id = ? and t1.object_id = t2.id and t1.is_deleted = 0 and t1.is_active = 1 and t1.is_403 = 0 and t1.is_404 = 0
                limit 1', array($domainId))->toArray();
        }
               
        $result['link'] = $this->request->getRequestUri();
        $result['statusCode'] = Response::STATUS_CODE_200;
        
        if (empty($sqlRes)) {
            $result['statusCode'] = Response::STATUS_CODE_404;
            
            $sqlRes = $this->db->query('
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
            
                $sqlRes = $this->db->query('
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
        
        if (null !== $this->params->fromRoute('lang')) {
            $lang = (string)$this->params->fromRoute('lang');
            $sqlRes = $this->db->query('select id from ' . DB_PREF . 'langs where prefix = ?', array($lang))->toArray();
            if (!empty($sqlRes)) {
                $result['language'] = $lang;
            }            
        } else {
            $sqlRes = $this->db->query('select prefix from ' . DB_PREF . 'langs where id = ?', array($result['lang_id']))->toArray();
            if (!empty($sqlRes)) {
                $result['language'] = $sqlRes[0]['prefix'];
            }
        }
        
        
        $object = $this->objectsCollection->getObject($result['object_id']);            
        $objectTypeId = $object->getTypeId();
        $objectType = $this->objectTypesCollection->getType($objectTypeId);

        $fieldGroups = $objectType->getFieldGroups();

        $result['fieldGroups'] = array();
        foreach ($fieldGroups as $k=>$fieldsGroup) {
            $groupName = $fieldsGroup->getName();
            $result['fieldGroups'][$groupName] = $fieldsGroup->getGroupData();
            $result['fieldGroups'][$groupName]['title'] = $this->translator->translateI18n($result['fieldGroups'][$groupName]['title']);
            
            $fields = $fieldsGroup->getFields();
            $result['fieldGroups'][$groupName]['fields'] = array();
            foreach ($fields as $field) {
                $property = $this->objectPropertyCollection->getProperty($result['object_id'], $field->getId());
                $result['fieldGroups'][$groupName]['fields'][$field->getName()] = $property->getValue();
            }                
        }
        

        $sqlRes = $this->db->query('select id, name from ' . DB_PREF . 'template_markers where template_id = ?', array($result['template']))->toArray();

        $markers = array();
        foreach ($sqlRes as $row) {
            $markers[$row['id']] = $row['name'];
        }

        $sqlRes = $this->db->query('select * from ' . DB_PREF . 'page_content_types', array())->toArray();

        $pageContentTypes = array();
        foreach ($sqlRes as $row) {
            $pageContentTypes[$row['id']] = $row;
        }

        $sqlRes = $this->db->query('
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
                $object = $this->objectsCollection->getObject($row['object_id']);            
                $objectTypeId = $object->getTypeId();
                $objectType = $this->objectTypesCollection->getType($objectTypeId);

                $fieldGroups = $objectType->getFieldGroups();

                $row['fieldGroups'] = array();
                foreach ($fieldGroups as $k=>$fieldsGroup) {
                    $groupName = $fieldsGroup->getName();
                    $row['fieldGroups'][$groupName] = $fieldsGroup->getGroupData();
                    $row['fieldGroups'][$groupName]['title'] = $this->translator->translateI18n($row['fieldGroups'][$groupName]['title']);

                    $fields = $fieldsGroup->getFields();
                    $row['fieldGroups'][$groupName]['fields'] = array();
                    foreach ($fields as $field) {
                        $property = $this->objectPropertyCollection->getProperty($row['object_id'], $field->getId());
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
                    throw new \Exception(get_class($instance) . ' does not implements \Pages\Entity\FeContentMethodInterface');
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
        $this->init();
        
        $sqlRes = $this->db->query('
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
        $this->init();
        
        $currentRoles = $this->usersIdentityProvider->getIdentityRoles();
        
        $checkRoles = explode(',', $access);
        
        foreach ($checkRoles as $v) {
            if ($v == '-2') {
                return true;
            } elseif ($v == '-1') {
                if ($this->usersAuthService->hasIdentity()) {
                    return true;
                }
            } elseif ($v == '0') {
                if (!$this->usersAuthService->hasIdentity()) {
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