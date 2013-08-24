<?php

namespace Pages\Model;

use Zend\Form\Factory;
use Zend\Db\Sql\Sql;

class PagesContent
{
    protected $serviceManager;
    
    protected $configManager;
    
    protected $db;
    
    protected $markersTable = 'template_markers';
    
    protected $pagesContentTable = 'pages_content';
    
    protected $pagesTable = 'pages';
    
    protected $markerId;
    
    protected $objectTypeId;
    
    protected $pageContentTypeId;
    
    protected $pageContentData;
    
    protected $pageId;
    
    protected $moduleManager;
    
    protected $fieldsCollection;
    
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
        $this->fieldsCollection = $sm->get('fieldsCollection');
    }
    
    public function setMarkerId($markerId)
    {
        $this->markerId = $markerId;
        return $this;
    }
    
    public function setObjectTypeId($typeId)
    {
        $this->objectTypeId = $typeId;
        return $this;
    }
    
    public function setPageContentTypeId($typeId)
    {
        $this->pageContentTypeId = $typeId;
        return $this;
    }
    
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
        return $this;
    }
    
    public function getPageContentData()
    {
        return $this->pageContentData;
    }
    
    public function getPageContentForm($pageContentId = null)
    {
        $pageContentTypeId = $this->pageContentTypeId;
        $objectTypeId = $this->objectTypeId;
        
        if (null === $pageContentId) {
            $formConfig = $this->getContentBaseFormConfig($pageContentTypeId, $objectTypeId);  
        
            if (null !== $objectTypeId) {
                $objectType = $this->objectTypesCollection->getType($objectTypeId);            
                $formConfig = $objectType->getAppFormConfig($formConfig);
            }
            
            $formValues = array();
            
            $contentData = array();
            $contentData['page_content_type_id'] = $pageContentTypeId;
            $contentData['object_type_id'] = $objectTypeId;
            $contentData['is_active'] = '1'; 
            $contentData['access'] = array('-2');
            $this->pageContentData = $contentData;   
            
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {
                    if (isset($contentData[$k2])) {
                        $formValues[$k][$k2] = $contentData[$k2];
                    }
                }
            }
            
        } else {  
            $sqlRes = $this->db->query('
                select * 
                from ' . DB_PREF . $this->pagesContentTable . ' 
                where id = ?', array($pageContentId))->toArray();
            
            if (empty($sqlRes)) {
                throw new \Exception('page content ' . $pageContentId . ' not found');
            }
            
            $objectId = $sqlRes[0]['object_id'];
            $object = $this->objectsCollection->getObject($objectId);
            
            $formValues = array();
            
            $contentData = $sqlRes[0];
            
            if (null === $pageContentTypeId) {
                $pageContentTypeId = $contentData['page_content_type_id'];
            }
            if ($pageContentTypeId == $contentData['page_content_type_id'] && null === $objectTypeId) {
                $objectTypeId = $object->getTypeId();
            }            
            
            $formConfig = $this->getContentBaseFormConfig($pageContentTypeId, $objectTypeId);  
        
            if (null !== $objectTypeId) {
                $objectType = $this->objectTypesCollection->getType($objectTypeId);            
                $formConfig = $objectType->getAppFormConfig($formConfig);
            }
            
            $contentData['name'] = $object->getName();
            $contentData['page_content_type_id'] = $pageContentTypeId;
            $contentData['object_type_id'] = $objectTypeId;
            $contentData['access'] = explode(',', $contentData['access']);
                        
            $this->pageContentData = $contentData;  
            
            foreach ($formConfig['fieldsets'] as $k=>$v) {
                foreach ($v['spec']['elements'] as $k2=>$v2) {
                    if ('field_' == substr($k2, 0, 6)) {
                        $fieldId = substr($k2, 6);
                        
                        $field = $this->fieldsCollection->getField($fieldId);
                        
                        if ($this->moduleManager->isModuleActive('Comments') && 
                            $field->isExists() && 
                            $field->getName() == 'allow_comments'
                            ) {                            
                            $commentsService = $this->serviceManager->get('Comments\Service\Comments');
                            $formValues[$k][$k2] = $commentsService->isAllowedComments($objectId);                            
                        } else {
                            $property = $this->objectPropertyCollection->getProperty($objectId, $fieldId); 
                            $formValues[$k][$k2] = $property->getValue();
                        }                        
                    } else {
                        if (isset($contentData[$k2])) {
                            $formValues[$k][$k2] = $contentData[$k2];
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
    
    protected function getContentBaseFormConfig(&$pageContentTypeId, &$objectTypeId)
    {
        $sqlRes = $this->db->query('select id, title, module from ' . DB_PREF . 'page_content_types', array())->toArray();
                
        $pageContentTypesMultiOptions = array();        
        foreach ($sqlRes as $row) {
            if (!isset($pageContentTypesMultiOptions[$row['module']])) {
                $moduleConfig = $this->moduleManager->getModuleConfig($row['module']);
                $moduleTitle = $this->translator->translateI18n($moduleConfig['title']);
                $pageContentTypesMultiOptions[$row['module']] = array(
                    'label' => $moduleTitle,
                );
            }            
            $pageContentTypesMultiOptions[$row['module']]['options'][$row['id']] = $this->translator->translateI18n($row['title']);
        }
        
        if (null === $pageContentTypeId) {
            foreach ($pageContentTypesMultiOptions as $k=>$v) {
                foreach ($v['options'] as $k2=>$v2) {
                    $pageContentTypeId = $k2;
                    break;
                }
                break;
            }
        }
        
        $sqlRes = $this->db->query('select * from ' . DB_PREF . 'page_content_types where id = ?', array($pageContentTypeId))->toArray();
        
        $templateMultiOptions = array();
        $templateType = 'content_template';
        $module = null;
        $method = null;
        if (!empty($sqlRes)) {    
            $module = $sqlRes[0]['module'];
            $method = $sqlRes[0]['method'];
            
            $sqlRes = $this->db->query('
                select id, title
                from ' . DB_PREF . 'templates
                where type = ?
                    and module = ?
                    and method = ?
            ', array($templateType, $module, $method))->toArray();
            
            foreach ($sqlRes as $row) {
                $templateMultiOptions[$row['id']] = $this->translator->translateI18n($row['title']);
            }
        }
        
        
        $sqlRes = $this->db->query('
            select id, name
            from ' . DB_PREF . 'roles
            order by sorting
        ', array())->toArray();
                
        $accessMultOptions = array(
            '-2' => 'Всем пользователям',
            '-1' => 'Авторизованным пользователям',
            '0' => 'Неавторизованным пользователям',            
        );
        foreach ($sqlRes as $row) {
            $accessMultOptions[$row['id']] = $row['name'];
        }
        
        
        $sqlRes = $this->db->query('
            select id, name
            from ' . DB_PREF . 'object_types
            where page_content_type_id = ?
        ', array($pageContentTypeId));
        
        
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
                            'label' => $this->translator->translate('Pages:Content common params fields group'),
                        ),
                        'elements' => array(
                            'page_content_type_id' => array(
                                'spec' => array(
                                    'type' => 'Zend\Form\Element\Select',
                                    'name' => 'page_content_type_id',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Content type'),
                                        'value_options' => $pageContentTypesMultiOptions,
                                    ),
                                    'attributes' => array(
                                        'id' => 'page_content_type_id',
                                    ),
                                ),
                            ),
                            'object_type_id' => array(
                                'spec' => array(
                                    'type' => 'ObjectTypeLink',
                                    'name' => 'object_type_id',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Content data type'),
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
                                        'label' => $this->translator->translate('Pages:Content is active field'),
                                    ),
                                ),
                            ),
                            'name' => array(
                                'spec' => array(
                                    'name' => 'name',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Content name'),                                        
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
                            'label' => $this->translator->translate('Pages:Content additional params fields group'),
                        ),
                        'elements' => array(
                            'template' => array(
                                'spec' => array(
                                    'type' => 'templateLink',
                                    'name' => 'template',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Content template field'),
                                        'value_options' => $templateMultiOptions,
                                        'module' => $module,
                                        'method' => $method,
                                    ),
                                    'attributes' => array(
                                        'id' => 'template',
                                    ),
                                ),
                            ),
                            'access' => array(
                                'spec' => array(
                                    'type' => 'Zend\Form\Element\Select',
                                    'name' => 'access',
                                    'options' => array(
                                        'label' => $this->translator->translate('Pages:Content access field'),
                                        'value_options' => $accessMultOptions,
                                    ),
                                    'attributes' => array(
                                        'multiple' => true,
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
            ),
        );
        
        return $baseFormConfig;
    }
    
    
    
    
    public function addContent($beforeContentId, $data)
    {
        $data = (array)$data;
        
        $result = array();
                
        $markerId = $this->markerId;  
        $pageId = $this->pageId;
        
        if (null === $markerId || null === $pageId) {
            throw new \Exception('wrong parameterss transferred');
        } else {
            $sqlRes = $this->db->query('select id from ' . DB_PREF . $this->markersTable . ' where id = ?', array($markerId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('marker ' . $markerId . ' does not found');
            }    
            
            $sqlRes = $this->db->query('select id from ' . DB_PREF . $this->pagesTable . ' where id = ?', array($pageId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('page ' . $pageId . ' does not found');
            }   
        }
        
        $tmp = $this->getPageContentForm();
              
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($tmp['formConfig']);     
        
        foreach ($data as $k=>$v) {
            if (isset($v['name']) && '' == $v['name']) {
                $data[$k]['name'] = $this->translator->translate('Pages:(Page content without name)');
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
            $objectType = $this->objectTypesCollection->getType($objectTypeId);
            
            unset($insertBase['name']);
            unset($insertBase['object_type_id']);
            
            $insertBase['page_id'] = $pageId;
            $insertBase['marker'] = $markerId;            
            $insertBase['object_id'] = $objectId;
            $insertBase['is_deleted'] = 0;
            
            $sql = new Sql($this->db);
            $insert = $sql->insert(DB_PREF . 'pages_content')->values($insertBase);
            $sql->prepareStatementForSqlObject($insert)->execute();    

            $contentId = $this->db->getDriver()->getLastGeneratedValue();

            $this->sortContent($beforeContentId, $contentId, $markerId);


            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $fields = $v->getFields();

                foreach ($fields as $k2=>$v2) {                    
                    if (isset($insertFields[$k2])) {                                                
                        if ($this->moduleManager->isModuleActive('Comments') && 
                            $v2->isExists() && 
                            $v2->getName() == 'allow_comments'
                            ) {                            
                            $commentsService = $this->serviceManager->get('Comments\Service\Comments');
                            
                            if ($insertFields[$k2]) {
                                $commentsService->allowComments($objectId);
                            } else {
                                $commentsService->disallowComments($objectId);
                            }
                        } else {
                            $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                            $property->setValue($insertFields[$k2])->save();
                        }
                    }
                }
            }
                                    
            $result['contentId'] = $contentId;
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
    }
    
    public function editContent($contentId, $data)
    {
        $data = (array)$data;
        
        $result = array();
                
        $tmp = $this->getPageContentForm($contentId);
              
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($tmp['formConfig']);     
        
        foreach ($data as $k=>$v) {
            if (isset($v['name']) && '' == $v['name']) {
                $data[$k]['name'] = $this->translator->translate('Pages:(Page content without name)');
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
            
            $tmp = $this->getPageContentData();
            $objectId = $tmp['object_id'];            
            
            $objectTypeId = $insertBase['object_type_id'];
            
            $object = $this->objectsCollection->getObject($objectId);            
            $object->setName($insertBase['name'])->setTypeId($objectTypeId)->save();
            
            unset($insertBase['name']);
            unset($insertBase['object_type_id']);

            $sql = new Sql($this->db);
            $update = $sql->update(DB_PREF . 'pages_content')->set($insertBase)->where('id = ' . (int)$contentId);
            $sql->prepareStatementForSqlObject($update)->execute();    

            $objectType = $this->objectTypesCollection->getType($objectTypeId); 
            $tmpFieldGroups = $objectType->getFieldGroups();
            foreach ($tmpFieldGroups as $k=>$v) {
                $fields = $v->getFields();

                foreach ($fields as $k2=>$v2) {                    
                    if (isset($insertFields[$k2])) {
                        if ($this->moduleManager->isModuleActive('Comments') && 
                            $v2->isExists() && 
                            $v2->getName() == 'allow_comments'
                            ) {                            
                            $commentsService = $this->serviceManager->get('Comments\Service\Comments');
                            
                            if ($insertFields[$k2]) {
                                $commentsService->allowComments($objectId);
                            } else {
                                $commentsService->disallowComments($objectId);
                            }
                        } else {
                            $property = $this->objectPropertyCollection->getProperty($objectId, $k2);                        
                            $property->setValue($insertFields[$k2])->save();
                        }
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
    
    public function deactivateContent($contentId)
    {
        $sqlRes = $this->db->query('select is_active from ' . DB_PREF . $this->pagesContentTable . ' where id = ? and is_deleted = 0', array($contentId))->toArray();
                        
        if (empty($sqlRes)) {
            return false;
        } else {
            if (!$sqlRes[0]['is_active']) {
                return true;
            } else {
                $this->db->query('update ' . DB_PREF . $this->pagesContentTable . ' set is_active = 0 where id = ?', array($contentId));

                return true;
            }
        }    
    }
    
    public function activateContent($contentId)
    {
        $sqlRes = $this->db->query('select is_active from ' . DB_PREF . $this->pagesContentTable . ' where id = ? and is_deleted = 0', array($contentId))->toArray();
                        
        if (empty($sqlRes)) {
            return false;
        } else {
            if ($sqlRes[0]['is_active']) {
                return true;
            } else {
                $this->db->query('update ' . DB_PREF . $this->pagesContentTable . ' set is_active = 1 where id = ?', array($contentId));

                return true;
            }
        }      
    }
    
    public function deleteContent($contentId)
    {
        $sqlRes = $this->db->query('select count(id) as cnt from ' . DB_PREF . $this->pagesContentTable . ' where id = ? and is_deleted = 0', array($contentId))->toArray();
            
        if ($sqlRes[0]['cnt'] == 0) {
            return false;
        } else {
            $this->db->query('update ' . DB_PREF . $this->pagesContentTable . ' set is_deleted = 1 where id = ?', array($contentId));

            return true;
        }      
    }
    
    public function sortContent($beforeContentId, $contentId, $markerId)
    {
        if (0 == $beforeContentId) {
            $contentSorting = 0;
        }
        else {
            $sqlRes = $this->db->query('select sorting from ' . DB_PREF . $this->pagesContentTable . ' where id = ?', array($beforeContentId))->toArray();

            if (empty($sqlRes)) {
                return;
            }
            $contentSorting = $sqlRes[0]['sorting'] + 1;
        }

        $this->db->query('UPDATE ' . DB_PREF . $this->pagesContentTable . '
            SET sorting = (sorting + 1)
            WHERE marker = ? AND sorting >= ?', array($markerId, $contentSorting));

        $this->db->query('update ' . DB_PREF . $this->pagesContentTable . '
            set sorting = ?, marker = ?
            where id = ?
        ', array($contentSorting, $markerId, $contentId));
    }
}