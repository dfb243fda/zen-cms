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
    
    public function getForm()
    {
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
            $form = $this->serviceManager->get('FormElementManager')
                                         ->get('Pages\Form\ContentBase', array('contentTypeId' => $contentTypeId));  
            
            if (null === $objectTypeId) {
                $valueOptions = $form->get('common')->get('object_type_id')->getValueOptions();
                
                if (!empty($valueOptions)) {
                    reset($valueOptions);
                    $objectTypeId = key($valueOptions);
                }
            }
            
            if (null !== $objectTypeId) {
                $objectType = $objectTypesCollection->getType($objectTypeId);       
                $this->mergeForms($form, $objectType->getForm());
            }  
            
            $contentData = array();
            $contentData['page_content_type_id'] = $contentTypeId;
            $contentData['object_type_id'] = $objectTypeId;
            $contentData['is_active'] = '1';     
            $contentData['access'] = array(-2);
            
                        
            $this->contentData = $contentData;
            
            foreach ($form->getFieldsets() as $fieldset) {
                foreach ($fieldset->getElements() as $element) {
                    $elementName = $element->getName();
                                        
                    if (isset($contentData[$elementName])) {
                        $element->setValue($contentData[$elementName]);
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
            
            $form = $this->serviceManager->get('FormElementManager')
                                         ->get('Pages\Form\ContentBase', array('contentTypeId' => $contentTypeId));  
            
            if (null === $objectTypeId) {
                $valueOptions = $form->get('common')->get('object_type_id')->getValueOptions();
                
                if (!empty($valueOptions)) {
                    reset($valueOptions);
                    $objectTypeId = key($valueOptions);
                    $this->setObjectTypeId($objectTypeId);
                }
            }            
            
            if (null !== $objectTypeId) {
                $objectType = $objectTypesCollection->getType($objectTypeId);       
                $this->mergeForms($form, $objectType->getForm());
            }     
            
            $contentData['name'] = $object->getName();
            $contentData['page_content_type_id'] = $contentTypeId;
            $contentData['object_type_id'] = $objectTypeId;
            $contentData['access'] = explode(',', $contentData['access']);
            
            $this->contentData = $contentData;
            
            foreach ($form->getFieldsets() as $fieldset) {
                foreach ($fieldset->getElements() as $element) {
                    $elementName = $element->getName();
                    
                    if ('field_' == substr($elementName, 0, 6)) {                        
                        $fieldId = substr($elementName, 6);                        
                        $field = $fieldsCollection->getField($fieldId);
                        
                        if ($moduleManager->isModuleActive('Comments') && 
                            $field->isExists() && 
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
        
        return $form;
    }
    
    protected function mergeForms(\Zend\Form\Form $form1, \Zend\Form\Form $form2)
    {
        foreach ($form2->getFieldsets() as $fieldset) {
            if ($form1->has($fieldset->getName())) {
                foreach ($fieldset->getElements() as $element) {
                    if (!$form1->get($fieldset->getName())->has($element->getName())) {
                        $form1->get($fieldset->getName())->add($element);
                    }                    
                }                
            } else {
                $form1->add($fieldset);
            }            
        }
        
        foreach ($form2->getInputFilter()->getInputs() as $inputFilterKey=>$inputFilter) {            
            if (!$form1->getInputFilter()->has($inputFilterKey)) {                
                $form1->getInputFilter()->add($inputFilter, $inputFilterKey);                
            } else {
                foreach ($inputFilter->getInputs() as $inputKey=>$input) {
                    $form1->getInputFilter()->get($inputFilterKey)->add($input, $inputKey);
                }  
            }                      
        }      
    }
}