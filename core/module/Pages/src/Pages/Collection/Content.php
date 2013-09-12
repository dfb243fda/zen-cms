<?php

namespace Pages\Collection;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;

class Content implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $markerId;
    
    protected $beforeContentId;
    
    protected $pageId;
    
    protected $objectTypeId;
    
    protected $contentTypeId;
    
    protected $markersTable = 'template_markers';
    
    protected $pagesTable = 'pages';
    
    protected $contentTable = 'pages_content';
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setMarkerId($markerId)
    {
        $this->markerId = $markerId;
        return $this;
    }
    
    public function setBeforeContentId($beforeContentId)
    {
        $this->beforeContentId = $beforeContentId;
        return $this;
    }
    
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
        return $this;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function setContentTypeid($contentTypeId)
    {
        $this->contentTypeId = $contentTypeId;
        return $this;
    }
    
    public function getForm()
    {
        $formFactory = $this->serviceManager->get('Pages\FormFactory\Content');
        
        $formFactory->setContentTypeId($this->contentTypeId)
                    ->setObjectTypeId($this->objectTypeId);
        
        $form = $formFactory->getForm();
        
        return $form;
    }
    
    public function addContent($data)
    {
        $markerId = $this->markerId;  
        $pageId = $this->pageId;
        $beforeContentId = $this->beforeContentId;
        
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $db = $this->serviceManager->get('db');
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        if (null === $markerId || null === $pageId) {
            throw new \Exception('wrong parameterss transferred');
        } else {
            $sqlRes = $db->query('select id from ' . DB_PREF . $this->markersTable . ' where id = ?', array($markerId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('marker ' . $markerId . ' does not found');
            }    
            
            $sqlRes = $db->query('select id from ' . DB_PREF . $this->pagesTable . ' where id = ?', array($pageId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('page ' . $pageId . ' does not found');
            }   
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
        
        $insertBase['page_id'] = $pageId;
        $insertBase['marker'] = $markerId;            
        $insertBase['object_id'] = $objectId;
        $insertBase['is_deleted'] = 0;

        $sql = new Sql($db);
        $insert = $sql->insert(DB_PREF . $this->contentTable)->values($insertBase);
        $sql->prepareStatementForSqlObject($insert)->execute();    

        $contentId = $db->getDriver()->getLastGeneratedValue();

        $contentEntity = $this->serviceManager->get('Pages\Entity\Content');
        
        $contentEntity->setContentId($contentId)->sortContent($beforeContentId, $markerId);
        
        
        $objectType = $objectTypesCollection->getType($objectTypeId); 
        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (isset($insertFields[$k2])) {
                    if ($moduleManager->isModuleActive('Comments') && 
                        $v2->getName() == 'allow_comments'
                        ) {                            
                        $commentsService = $this->serviceManager->get('Comments\Service\Comments');

                        if ($insertFields[$k2]) {
                            $commentsService->allowComments($objectId);
                        } else {
                            $commentsService->disallowComments($objectId);
                        }
                    } else {
                        $property = $objectPropertyCollection->getProperty($objectId, $k2);                        
                        $property->setValue($insertFields[$k2])->save();
                    }
                }
            }
        }

        return $contentId;
    }
    
    public function deleteContent($contentId)
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select count(id) as cnt from ' . DB_PREF . $this->contentTable . ' where id = ? and is_deleted = 0', array($contentId))->toArray();
            
        if ($sqlRes[0]['cnt'] == 0) {
            return false;
        } else {
            $db->query('update ' . DB_PREF . $this->contentTable . ' set is_deleted = 1 where id = ?', array($contentId));

            return true;
        }      
    }
    
}