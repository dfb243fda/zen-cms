<?php

namespace Pages\Model;

use Zend\Form\Factory;
use Zend\Db\Sql\Sql;

class Pages
{
    protected $serviceManager;
    
    protected $configManager;
    
    protected $db;
    
    protected $domainsTable = 'domains';
    
    protected $pagesTable = 'pages';
    
    protected $pagesContentTable = 'pages_content';
    
    protected $domainId;
    
    protected $parentPageId;
    
    protected $objectTypeId;
    
    protected $pageTypeId;
    
    protected $pageData;    
    
    protected $moduleManager;
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        $this->configManager = $sm->get('configManager');
        $this->db = $sm->get('db');
        $this->translator = $sm->get('translator');
        $this->objectTypesCollection = $sm->get('objectTypesCollection');
        $this->objectsCollection = $sm->get('objectsCollection');
        $this->objectPropertyCollection = $sm->get('objectPropertyCollection');
        $this->moduleManager = $sm->get('moduleManager');
    }
    
    public function getPages()
    {
        $items = array();
        
        $pages = $this->db
                ->query('
                    SELECT p1.*,
                        (SELECT count(p2.id) FROM ' . DB_PREF . 'pages p2 WHERE p2.parent_id = p1.id AND is_deleted=0) AS children_cnt,
                        (SELECT o.name FROM ' . DB_PREF . 'objects o WHERE o.id=p1.object_id) AS name
                    FROM ' . DB_PREF . 'pages p1
                    WHERE p1.parent_id = ? AND p1.is_deleted=0
                    ORDER BY p1.sorting
                    ', array($parentId))
                ->toArray();
        
        foreach ($pages as $row) {
            if ($row['children_cnt'] > 0) {
                $row['state'] = 'closed';
            }
            else {
                $row['state'] = 'open';
            }
            $items[] = $row;
        }
        
        return $items;
    }
    
    public function getDomains()
    {
        $items = array();
        
        $domains = $this->db->query('SELECT *, host AS name FROM ' . DB_PREF . $this->domainsTable, array())->toArray();
        
        foreach ($domains as $row) {                    
            $pages = $this->db
                    ->query('
                        SELECT p1.*,
                            (SELECT count(p2.id) FROM ' . DB_PREF . 'pages p2 WHERE p2.parent_id = p1.id AND is_deleted=0) AS children_cnt,
                            (SELECT o.name FROM ' . DB_PREF . 'objects o WHERE o.id=p1.object_id) AS name
                        FROM ' . DB_PREF . 'pages p1
                        WHERE p1.parent_id = 0 AND p1.domain_id=? AND p1.is_deleted=0
                        ORDER BY p1.sorting
                        ', array($row['id']))
                    ->toArray();
            
            if (empty($pages)) {
                $row['state'] = 'open';
            } else {
                $row['state'] = 'closed';
            }
            
            foreach ($pages as $row2) {
                if ($row2['children_cnt'] > 0) {
                    $row2['state'] = 'closed';
                }
                else {
                    $row2['state'] = 'open';
                }
                
                $row['children'][] = $row2;
            }
            
            $row['domain_id'] = $row['id'];
            $row['id'] = 'domain_' . $row['id'];
            $items[] = $row;
        }
        
        return $items;
    }
    
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
        return $this;
    }
    
    public function setParentPageId($pageId)
    {
        $this->parentPageId = $pageId;
        return $this;
    }
    
    public function setObjectTypeId($typeId)
    {
        $this->objectTypeId = $typeId;
        return $this;
    }
    
    public function setPageTypeid($typeId)
    {
        $this->pageTypeId = $typeId;
        return $this;
    }
    
    public function getPageData()
    {
        return $this->pageData;
    }
    
    public function getPageForm($pageId = null)
    {
        $pageTypeId = $this->pageTypeId;
        $objectTypeId = $this->objectTypeId;        
        
        if (null === $pageId) {
            $formConfig = $this->getPageBaseFormConfig($pageTypeId, $objectTypeId);  
        
            if (null !== $objectTypeId) {
                $objectType = $this->objectTypesCollection->getType($objectTypeId);            
                $formConfig = $objectType->getAppFormConfig($formConfig);
            }
            
            $formValues = array();
            
            $pageData = array();
            $pageData['page_type_id'] = $pageTypeId;
            $pageData['object_type_id'] = $objectTypeId;
            $pageData['is_active'] = '1';     
            $pageData['access'] = array(-2);
            
            $sqlRes = $this->db->query('select default_lang_id from ' . DB_PREF . 'domains where id = ?', array($this->domainId))->toArray();
            if (!empty($sqlRes)) {
                $pageData['lang_id'] = $sqlRes[0]['default_lang_id'];
            }
            
            $this->pageData = $pageData;   
            
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {
                    if (isset($pageData[$k2])) {
                        $formValues[$k][$k2] = $pageData[$k2];
                    }
                }
            }
            
        } else {
            $sqlRes = $this->db->query('
                select * 
                from ' . DB_PREF . $this->pagesTable . ' 
                where id = ?', array($pageId))->toArray();
            
            if (empty($sqlRes)) {
                throw new \Exception('page ' . $pageId . ' not found');
            }
            
            $objectId = $sqlRes[0]['object_id'];
            $object = $this->objectsCollection->getObject($objectId);
            
            $formValues = array();
            
            $pageData = $sqlRes[0];
            
            if (null === $pageTypeId) {
                $pageTypeId = $pageData['page_type_id'];
            }
            if ($pageTypeId == $pageData['page_type_id'] && null === $objectTypeId) {
                $objectTypeId = $object->getTypeId();
            }
            
            $formConfig = $this->getPageBaseFormConfig($pageTypeId, $objectTypeId);  
        
            if (null !== $objectTypeId) {
                $objectType = $this->objectTypesCollection->getType($objectTypeId);            
                $formConfig = $objectType->getAppFormConfig($formConfig);
            }
            
            $pageData['name'] = $object->getName();
            $pageData['page_type_id'] = $pageTypeId;
            $pageData['object_type_id'] = $objectTypeId;
            $pageData['access'] = explode(',', $pageData['access']);
            $this->pageData = $pageData;  
            
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {
                    if ('field_' == substr($k2, 0, 6)) {
                        $fieldId = substr($k2, 6);
                        $property = $this->objectPropertyCollection->getProperty($objectId, $fieldId); 
                        $formValues[$k][$k2] = $property->getValue();
                    } else {
                        if (isset($pageData[$k2])) {
                            $formValues[$k][$k2] = $pageData[$k2];
                        }
                    }                    
                }
            }
        }
        
        return array(
            'formConfig' => $formConfig,
            'formValues' => $formValues,
        );
    }
    
    public function addPage($data)
    {
        $data = (array)$data;
        
        $result = array();
        
        $parentId = $this->parentPageId;
        $domainId = $this->domainId;
        
        if (null === $parentId && null !== $domainId) {
            $parentId = 0;
            $sqlRes = $this->db->query('select id from ' . DB_PREF . $this->domainsTable . ' where id = ?', array($domainId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('domain ' . $domainId . ' does not found');
            }            
        } elseif (null !== $parentId && null === $domainId) {
            $sqlRes = $this->db->query('select domain_id from ' . DB_PREF . $this->pagesTable . ' where id = ?', array($parentId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('page ' . $parentId . ' does not found');
            }  
            $domainId = $sqlRes[0]['domain_id'];
        } else {
            throw new \Exception('wrong parameterss transferred');
        }
        
        $tmp = $this->getPageForm();
              
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($tmp['formConfig']);     
        
        foreach ($data as $k=>$v) {
            if (isset($v['name']) && '' == $v['name']) {
                $data[$k]['name'] = $this->translator->translate('Pages:(Page without name)');
            }
        }
        
        $form->setData($data);
        
        if ($form->isValid()) { 
            $data = $form->getData();
                
            $insertFields = array();
            $insertBase = array();

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
            
            $objectId = $this->objectsCollection->addObject($insertBase['name'], $insertBase['object_type_id']);
            $objectTypeId = $insertBase['object_type_id'];
            
            unset($insertBase['name']);
            unset($insertBase['object_type_id']);

            $insertBase['domain_id'] = $domainId;
            $insertBase['parent_id'] = $parentId;
            $insertBase['object_id'] = $objectId;
            $insertBase['is_deleted'] = 0;
            
            $sqlRes = $this->db->query('SELECT MAX(sorting) AS max_sorting FROM ' . DB_PREF . 'pages WHERE parent_id = ? AND domain_id = ?', array($parentId, $domainId))->toArray();            
            if (null === $sqlRes[0]['max_sorting']) {
                $insertBase['sorting'] = 0;              
            } else {
                $insertBase['sorting'] = $sqlRes[0]['max_sorting'] + 1;
            }
            
            if ($insertBase['is_default']) {
                $this->db->query('update ' . DB_PREF . 'pages set is_default = 0 where domain_id = ?',  array($domainId));
            }
            if ($insertBase['is_403']) {
                $this->db->query('update ' . DB_PREF . 'pages set is_403 = 0 where domain_id = ?',  array($domainId));
            }
            if ($insertBase['is_404']) {
                $this->db->query('update ' . DB_PREF . 'pages set is_404 = 0 where domain_id = ?',  array($domainId));
            }

            $sql = new Sql($this->db);
            $insert = $sql->insert(DB_PREF . 'pages')->values($insertBase);
            $sql->prepareStatementForSqlObject($insert)->execute();    

            $pageId = $this->db->getDriver()->getLastGeneratedValue();


            $objectType = $this->objectTypesCollection->getType($objectTypeId); 
            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $fields = $v->getFields();

                foreach ($fields as $k2=>$v2) {                    
                    if (isset($insertFields[$k2])) {
                        $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                        $property->setValue($insertFields[$k2])->save();
                    }
                }
            }
                                    
            $result['pageId'] = $pageId;
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
    }
    
    public function editPage($pageId, $data)
    {
        $data = (array)$data;
        
        $result = array();
                
        $tmp = $this->getPageForm($pageId);
              
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($tmp['formConfig']);     
        
        foreach ($data as $k=>$v) {
            if (isset($v['name']) && '' == $v['name']) {
                $data[$k]['name'] = $this->translator->translate('Pages:(Page without name)');
            }
        }
        
        $form->setData($data);
        
        if ($form->isValid()) { 
            $data = $form->getData();
                
            $insertFields = array();
            $insertBase = array();

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
            
            $tmp = $this->getPageData();
            $objectId = $tmp['object_id'];
            $domainId = $tmp['domain_id'];
            
            $objectTypeId = $insertBase['object_type_id'];
            
            $object = $this->objectsCollection->getObject($objectId);            
            $object->setName($insertBase['name'])->setTypeId($objectTypeId)->save();


            unset($insertBase['name']);
            unset($insertBase['object_type_id']);
            
            if ($insertBase['is_default']) {
                $this->db->query('update ' . DB_PREF . 'pages set is_default = 0 where domain_id = ?',  array($domainId));
            }
            if ($insertBase['is_403']) {
                $this->db->query('update ' . DB_PREF . 'pages set is_403 = 0 where domain_id = ?',  array($domainId));
            }
            if ($insertBase['is_404']) {
                $this->db->query('update ' . DB_PREF . 'pages set is_404 = 0 where domain_id = ?',  array($domainId));
            }
            
            $sql = new Sql($this->db);
            $update = $sql->update(DB_PREF . 'pages')->set($insertBase)->where('id = ' . (int)$pageId);
            $sql->prepareStatementForSqlObject($update)->execute();    

            $objectType = $this->objectTypesCollection->getType($objectTypeId); 
            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $fields = $v->getFields();

                foreach ($fields as $k2=>$v2) {                    
                    if (isset($insertFields[$k2])) {
                        $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                        $property->setValue($insertFields[$k2])->save();
                    }
                }
            }
            
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
    }
    
    public function deletePage($pageId)
    {            
        $sqlRes = $this->db->query('select id from ' . DB_PREF . $this->pagesTable . ' where id = ? and is_deleted = 0', array($pageId))->toArray();

        if (empty($sqlRes)) {
            return false;
        } else {
            $this->db->query('update ' . DB_PREF . $this->pagesTable . ' set is_deleted = 1 where id = ?', array($pageId));

            $sqlRes = $this->db->query('select id from ' . DB_PREF . $this->pagesTable . ' where parent_id = ? and is_deleted = 0', array($pageId));

            foreach ($sqlRes as $row) {
                $this->deletePage($row['id']);
            }

            $this->db->query('update ' . DB_PREF . $this->pagesContentTable . ' set is_deleted = 0 where page_id = ?', array($pageId));

            return true;
        }
    }
    
    protected function getPageBaseFormConfig(&$pageTypeId, &$objectTypeId)
    {
        $feTheme = $this->configManager->get('system', 'fe_theme');
                
        $templateType = 'page_template';
        
        $sqlRes = $this->db->query('select id, title from ' . DB_PREF . 'templates where type = ? and module = ?', array($templateType, $feTheme))->toArray();
        
        $templateMultiOptions = array();
        foreach ($sqlRes as $row) {
            $templateMultiOptions[$row['id']] = $this->translator->translateI18n($row['title']);
        }
        
        $sqlRes = $this->db->query('select id, name from ' . DB_PREF . 'roles order by sorting', array())->toArray();
                
        $accessMultOptions = array(
            '-2' => 'Всем пользователям',
            '-1' => 'Авторизованным пользователям',
            '0' => 'Неавторизованным пользователям',            
        );
        foreach ($sqlRes as $row) {
            $accessMultOptions[$row['id']] = $row['name'];
        }
        
        $sqlRes = $this->db->query('select id, title from ' . DB_PREF . 'langs', array())->toArray();
                
        $langMultiOptions = array();
        foreach ($sqlRes as $row) {
            $langMultiOptions[$row['id']] = $row['title'];
        }
        
        $sqlRes = $this->db->query('select id, title, module from ' . DB_PREF . 'page_types', array())->toArray();
        
        $pageTypesMultiOptions = array();
        foreach ($sqlRes as $row) {
            if (!isset($pageTypesMultiOptions[$row['module']])) {
                $moduleConfig = $this->moduleManager->getModuleConfig($row['module']);
                $moduleTitle = $this->translator->translateI18n($moduleConfig['title']);
                $pageTypesMultiOptions[$row['module']] = array(
                    'label' => $moduleTitle,
                );
            }                        
            $pageTypesMultiOptions[$row['module']]['options'][$row['id']] = $this->translator->translateI18n($row['title']);
        }
        
        if (null === $pageTypeId) {
            foreach ($pageTypesMultiOptions as $k=>$v) {
                foreach ($v['options'] as $k2=>$v2) {
                    $pageTypeId = $k2;
                    break;
                }
                break;
            }
        }
        
        
        $sqlRes = $this->db->query('select id, name from ' . DB_PREF . 'object_types where page_type_id = ?', array($pageTypeId))->toArray();
        
        $objectTypesMultiOptions = array();
        foreach ($sqlRes as $row) {
            $objectTypesMultiOptions[$row['id']] = $this->translator->translateI18n($row['name']);
        }
        
        if (null === $objectTypeId) {
            reset($objectTypesMultiOptions);
            $objectTypeId = key($objectTypesMultiOptions);
        }
        
        $baseFormConfig = array(
            'fieldsets' => array(
                'common' => array(
                    'spec' => array(
                        'name' => 'common',
                        'options' => array(
                            'label' => $this->translator->translate('Pages:Common params fields group'),
                        ),
                        'elements' => array(
                            'page_type_id' => array(
                                'spec' => array(
                                    'type' => 'Zend\Form\Element\Select',
                                    'name' => 'page_type_id',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Page type'),
                                        'value_options' => $pageTypesMultiOptions,
                                    ),
                                    'attributes' => array(
                                        'id' => 'page_type_id',
                                    ),
                                ),
                            ),
                            'object_type_id' => array(
                                'spec' => array(
                                    'type' => 'ObjectTypeLink',
                                    'name' => 'object_type_id',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Data type'),
                                        'value_options' => $objectTypesMultiOptions,
                                    ),
                                    'attributes' => array(
                                        'id' => 'object_type_id',
                                    ),
                                ),
                            ),
                            'is_active' => array(
                                'spec' => array(
                                    'type' => 'Zend\Form\Element\Checkbox',
                                    'name' => 'is_active',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Activity'),
                                    ),
                                ),
                            ),
                            'name' => array(
                                'spec' => array(
                                    'name' => 'name',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Page name'),                                        
                                    ),
                                    'attributes' => array(
                                        'type' => 'text',
                                    ),
                                ),
                            ),
                            'alias' => array(
                                'spec' => array(
                                    'name' => 'alias',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Page alias (For URL)'),
                                    ),
                                    'attributes' => array(
                                        'type' => 'text',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'additional_params' => array(
                    'spec' => array(
                        'name' => 'additional_params',
                        'options' => array(
                            'label' => $this->translator->translate('Pages:Additional params fields group'),
                        ),
                        'elements' => array(
                            'template' => array(
                                'spec' => array(
                                    'type' => 'templateLink',
                                    'name' => 'template',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Template'),
                                        'value_options' => $templateMultiOptions,
                                        'module' => $feTheme,
                                    ),
                                    'attributes' => array(
                                        'id' => 'template',
                                    ),
                                ),
                            ),
                           'is_default' => array(
                                'spec' => array(
                                    'type' => 'Zend\Form\Element\Checkbox',
                                    'name' => 'is_default',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Is default field'),
                                    ),
                                ),
                            ),
                            'access' => array(
                                'spec' => array(
                                    'type' => 'Zend\Form\Element\Select',
                                    'name' => 'access',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Access field'),
                                        'value_options' => $accessMultOptions,
                                    ),
                                    'attributes' => array(
                                        'multiple' => true,
                                    ),
                                ),
                            ),
                            'non_access_url' => array(
                                'spec' => array(
                                    'type' => 'text',
                                    'name' => 'non_access_url',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Non access url'),
                                    ),
                                ),
                            ),
                            'lang_id' =>array(
                                'spec' => array(
                                    'type' => 'Zend\Form\Element\Select',
                                    'name' => 'lang_id',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Language field'),
                                        'value_options' => $langMultiOptions,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                '403_404' => array(
                    'spec' => array(
                        'name' => '403_404',
                        'options' => array(
                            'label' => $this->translator->translate('Pages:403 404 fields group'),
                        ),
                        'elements' => array(
                            'is_403' => array(
                                'spec' => array(
                                    'name' => 'is_403',
                                    'type' => 'checkbox',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Is 403 page field'),
                                        'description' => $this->translator->translate('Pages:Is 403 page field description'),
                                    ),
                                ),
                            ),   
                            'is_404' => array(
                                'spec' => array(
                                    'name' => 'is_404',
                                    'type' => 'checkbox',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Is 404 page field'),
                                        'description' => $this->translator->translate('Pages:Is 404 page field description'),
                                    ),
                                ),
                            ),                            
                        ),
                    ),
                ),
            ),
            'input_filter' => array(                
                'common' => array(
                    'type' => 'Zend\InputFilter\InputFilter',
                    'name' => array(               
                        'required' => true,
                        'filters' => array(
                            array('name' => 'Zend\Filter\StringTrim'),
                        ),
                    ),
                ),
                'additional_params' => array(
                    'type' => 'Zend\InputFilter\InputFilter',
                    'non_access_url' => array(
                        'required' => false,
                    ),
                ),
            ),
        );
        
        return $baseFormConfig;
    }
}