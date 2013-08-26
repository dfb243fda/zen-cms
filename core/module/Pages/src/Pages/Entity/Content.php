<?php

namespace Pages\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;

class Content implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $contentTypeId;
    
    protected $objectTypeId;
    
    protected $contentId;
    
    protected $contentTable = 'pages_content';
    
    protected $formFactory;
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setContentTypeId($contentTypeId)
    {
        $this->contentTypeId = $contentTypeId;
        return $this;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function setContentId($contentId)
    {
        $this->contentId = $contentId;
        return $this;
    }
    
    public function getForm()
    {
        $this->formFactory = $formFactory = $this->serviceManager->get('Pages\FormFactory\Content');
        
        $formFactory->setContentTypeId($this->contentTypeId)
                    ->setObjectTypeId($this->objectTypeId)
                    ->setContentId($this->contentId);
        
        $form = $formFactory->getForm();
        
        return $form;
    }
    
    public function getContentFormData()
    {
        if (null === $this->formFactory) {
            throw new \Exception('form does not created yet, youy can create it with getForm() method');
        }
        
        return $this->formFactory->getContentData();
    }
    
    public function editContent($data)
    {
        $contentId = $this->contentId;
        
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $db = $this->serviceManager->get('db');
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        $insertFields = array(); //значения для свойств объектов (таблица object и т.д.)
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

        $tmp = $this->getContentFormData();
        $objectId = $tmp['object_id'];

        $objectTypeId = $insertBase['object_type_id'];

        $object = $objectsCollection->getObject($objectId);            
        $object->setName($insertBase['name'])->setTypeId($objectTypeId)->save();


        unset($insertBase['name']);
        unset($insertBase['object_type_id']);
        
        
        $sql = new Sql($db);
        
        $update = $sql->update(DB_PREF . 'pages_content')->set($insertBase)->where('id = ' . (int)$contentId);
        $sql->prepareStatementForSqlObject($update)->execute();    

        $objectType = $objectTypesCollection->getType($objectTypeId); 
        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (isset($insertFields[$k2])) {
                    if ($moduleManager->isModuleActive('Comments') && 
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
                        $property = $objectPropertyCollection->getProperty($objectId, $k2);                        
                        $property->setValue($insertFields[$k2])->save();
                    }
                }
            }
        }

        return true;
    }
    
    public function sortContent($beforeContentId, $markerId)
    {
        $db = $this->serviceManager->get('db');
        
        if (0 == $beforeContentId) {
            $contentSorting = 0;
        } else {
            $sqlRes = $db->query('select sorting from ' . DB_PREF . $this->contentTable . ' where id = ?', array($beforeContentId))->toArray();

            if (empty($sqlRes)) {
                return;
            }
            $contentSorting = $sqlRes[0]['sorting'] + 1;
        }

        $db->query('UPDATE ' . DB_PREF . $this->contentTable . '
            SET sorting = (sorting + 1)
            WHERE marker = ? AND sorting >= ?', array($markerId, $contentSorting));

        $db->query('update ' . DB_PREF . $this->contentTable . '
            set sorting = ?, marker = ?
            where id = ?
        ', array($contentSorting, $markerId, $this->contentId));
    }
    
    public function deactivateContent()
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select is_active from ' . DB_PREF . $this->contentTable . ' where id = ? and is_deleted = 0', array($this->contentId))->toArray();
                        
        if (empty($sqlRes)) {
            return false;
        } else {
            if (!$sqlRes[0]['is_active']) {
                return true;
            } else {
                $db->query('update ' . DB_PREF . $this->contentTable . ' set is_active = 0 where id = ?', array($this->contentId));

                return true;
            }
        }    
    }
    
    public function activateContent()
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select is_active from ' . DB_PREF . $this->contentTable . ' where id = ? and is_deleted = 0', array($this->contentId))->toArray();
                        
        if (empty($sqlRes)) {
            return false;
        } else {
            if ($sqlRes[0]['is_active']) {
                return true;
            } else {
                $db->query('update ' . DB_PREF . $this->contentTable . ' set is_active = 1 where id = ?', array($this->contentId));

                return true;
            }
        }      
    }
}