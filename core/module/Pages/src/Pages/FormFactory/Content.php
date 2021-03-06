<?php

namespace Pages\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Content implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $contentTypeId;
    
    protected $contentId;
    
    protected $contentData;
    
    protected $contentTable = 'pages_content';
    
    /**
     * GetForm() method will return form with data or not
     * @var boolean
     */
    protected $populateForm = true;
    
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
    
    public function setContentTypeid($typeId)
    {
        $this->contentTypeId = $typeId;
        return $this;
    }
    
    public function setContentId($contentId)
    {
        $this->contentId = $contentId;
        return $this;
    }
    
    public function getContentData()
    {
        return $this->contentData;
    }
    
    public function setPopulateForm($populateForm)
    {
        $this->populateForm = (bool)$populateForm;
        return $this;
    }
    
    public function getPopulateForm()
    {
        return $this->populateForm;
    }
    
    public function getForm()
    {
        $formsMerger = $this->serviceManager->get('App\Form\FormsMerger');
        
        $contentId = $this->contentId;
        $contentTypeId = $this->contentTypeId;
        $objectTypeId = $this->objectTypeId;
        
        $db = $this->serviceManager->get('db');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $fieldsCollection = $this->serviceManager->get('fieldsCollection');
        $moduleManager = $this->serviceManager->get('moduleManager');
        
        if (null === $this->contentId) {            
            $baseForm = $this->serviceManager->get('FormElementManager')
                                         ->get('Pages\Form\ContentBase', array('contentTypeId' => $contentTypeId));  
            
            if (null === $contentTypeId) {
                $contentTypeId = $baseForm->getContentTypeId();
                $this->setContentTypeId($contentTypeId);
            }
            
            if (null === $objectTypeId) {
                $valueOptions = $baseForm->get('common')->get('object_type_id')->getValueOptions();
                
                if (!empty($valueOptions)) {
                    reset($valueOptions);
                    $objectTypeId = key($valueOptions);
                    $this->setObjectTypeId($objectTypeId);
                }
            }
            
            $formsMerger->addForm($baseForm);
            
            if (null !== $objectTypeId) {
                $objectType = $objectTypesCollection->getType($objectTypeId);    
                $formsMerger->addForm($objectType->getForm());
            }  
            
            $contentData = array();
            $contentData['page_content_type_id'] = $contentTypeId;
            $contentData['object_type_id'] = $objectTypeId;
            $contentData['is_active'] = '1';     
            $contentData['access'] = array(-2);
            
                        
            $this->contentData = $contentData;
            
            $form = $formsMerger->getForm();
            
            if ($this->populateForm) {
                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($fieldset->getElements() as $element) {
                        $elementName = $element->getName();

                        if (isset($contentData[$elementName])) {
                            $element->setValue($contentData[$elementName]);
                        }
                    }
                }     
            }                   
        } else {
            
            $sqlRes = $db->query('
                select * 
                from ' . DB_PREF . $this->contentTable . ' 
                where id = ?', array($contentId))->toArray();
            
            if (empty($sqlRes)) {
                throw new \Exception('content ' . $contentId . ' not found');
            }
            
            $contentData = $sqlRes[0];
            
            if (null === $contentTypeId) {
                $contentTypeId = $contentData['page_content_type_id'];
                $this->setContentTypeId($contentTypeId);
            }
                        
            $objectId = $sqlRes[0]['object_id'];
            $object = $objectsCollection->getObject($objectId);
                        
            if ($contentTypeId == $contentData['page_content_type_id'] && null === $objectTypeId) {                
                $objectTypeId = $object->getTypeId();
                $this->setObjectTypeId($objectTypeId);
            }               
            
            $baseForm = $this->serviceManager->get('FormElementManager')
                                         ->get('Pages\Form\ContentBase', array('contentTypeId' => $contentTypeId));  
            
            if (null === $objectTypeId) {
                $valueOptions = $baseForm->get('common')->get('object_type_id')->getValueOptions();
                
                if (!empty($valueOptions)) {
                    reset($valueOptions);
                    $objectTypeId = key($valueOptions);
                    $this->setObjectTypeId($objectTypeId);
                }
            }            
            
            $formsMerger->addForm($baseForm);
            
            if (null !== $objectTypeId) {
                $objectType = $objectTypesCollection->getType($objectTypeId);       
                $formsMerger->addForm($objectType->getForm());
            }     
            
            $contentData['name'] = $object->getName();
            $contentData['page_content_type_id'] = $contentTypeId;
            $contentData['object_type_id'] = $objectTypeId;
            $contentData['access'] = explode(',', $contentData['access']);
            
            $this->contentData = $contentData;
            
            $form = $formsMerger->getForm();
            
            if ($this->populateForm) {
                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($fieldset->getElements() as $element) {
                        $elementName = $element->getName();

                        if ('field_' == substr($elementName, 0, 6)) {                        
                            $fieldId = substr($elementName, 6);                        
                            $field = $fieldsCollection->getField($fieldId);

                            if ($moduleManager->isModuleActive('Comments') && 
                                $field &&
                                $field->getName() == 'allow_comments'
                                ) {                            
                                $commentsService = $this->serviceManager->get('Comments\Service\Comments');
                                $element->setValue($commentsService->isAllowedComments($objectId));                            
                            } else {
                                $property = $objectPropertyCollection->getProperty($objectId, $fieldId); 
                                $element->setValue($property->getValue());
                            }                     
                        } else {
                            if (isset($contentData[$elementName])) {
                                $element->setValue($contentData[$elementName]);
                            }
                        }      
                    }
                }
            }            
        }
        
        return $form;
    }
}