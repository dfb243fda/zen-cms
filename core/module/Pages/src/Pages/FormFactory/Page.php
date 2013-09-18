<?php

namespace Pages\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Page implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $pageTypeId;
    
    protected $pageId;
    
    protected $pagesTable = 'pages';
    
    protected $pageData;
    
    protected $domainId;
    
    protected $parentPageId;
    
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
    
    public function setPageTypeid($typeId)
    {
        $this->pageTypeId = $typeId;
        return $this;
    }
    
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
        return $this;
    }
    
    public function getPageData()
    {
        return $this->pageData;
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
        
        $pageId = $this->pageId;
        $pageTypeId = $this->pageTypeId;
        $objectTypeId = $this->objectTypeId;
        
        $db = $this->serviceManager->get('db');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        
        if (null === $pageId) {            
            $baseForm = $this->serviceManager->get('FormElementManager')
                                         ->get('Pages\Form\PageBase', array('pageTypeId' => $pageTypeId));  
                        
            if (null === $pageTypeId) {
                $pageTypeId = $baseForm->getPageTypeId();
                $this->setPageTypeId($pageTypeId);
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
            
            $pageData = array();
            $pageData['page_type_id'] = $pageTypeId;
            $pageData['object_type_id'] = $objectTypeId;
            $pageData['is_active'] = '1';     
            $pageData['access'] = array(-2);
            
            if (null !== $this->parentPageId) {
                $sqlRes = $db->query('select lang_id from ' . DB_PREF . 'pages where id = ?', array($this->parentPageId))->toArray();
                if (!empty($sqlRes)) {
                    $pageData['lang_id'] = $sqlRes[0]['lang_id'];
                } else {
                    throw new \Exception('Page ' . $this->parentPageId . ' does not found');
                }
            } elseif (null !== $this->domainId) {                
                $sqlRes = $db->query('select default_lang_id from ' . DB_PREF . 'domains where id = ?', array($this->domainId))->toArray();
                if (!empty($sqlRes)) {
                    $pageData['lang_id'] = $sqlRes[0]['default_lang_id'];
                }
            } else {
                throw new \Exception('Parent page and domain does not defined');
            }
                        
            $this->pageData = $pageData;
            
            $form = $formsMerger->getForm();
            
            if ($this->populateForm) {
                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($fieldset->getElements() as $element) {
                        $elementName = $element->getName();

                        if (isset($pageData[$elementName])) {
                            $element->setValue($pageData[$elementName]);
                        }
                    }
                }       
            }                 
        } else {
            
            $sqlRes = $db->query('
                select * 
                from ' . DB_PREF . $this->pagesTable . ' 
                where id = ?', array($pageId))->toArray();
            
            if (empty($sqlRes)) {
                throw new \Exception('page ' . $pageId . ' not found');
            }
            
            $pageData = $sqlRes[0];
            
            if (null === $pageTypeId) {
                $pageTypeId = $pageData['page_type_id'];
                $this->setPageTypeId($pageTypeId);
            }
                        
            $objectId = $sqlRes[0]['object_id'];
            $object = $objectsCollection->getObject($objectId);
            
            if ($pageTypeId == $pageData['page_type_id'] && null === $objectTypeId) {
                $objectTypeId = $object->getTypeId();
                $this->setObjectTypeId($objectTypeId);
            }              
            
            $baseForm = $this->serviceManager->get('FormElementManager')
                                         ->get('Pages\Form\PageBase', array('pageTypeId' => $pageTypeId));  
            
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
            
            $pageData['name'] = $object->getName();
            $pageData['page_type_id'] = $pageTypeId;
            $pageData['object_type_id'] = $objectTypeId;
            $pageData['access'] = explode(',', $pageData['access']);
            
            $this->pageData = $pageData;
            
            $form = $formsMerger->getForm();
            
            if ($this->populateForm) {
                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($fieldset->getElements() as $element) {
                        $elementName = $element->getName();

                        if ('field_' == substr($elementName, 0, 6)) {
                            $fieldId = substr($elementName, 6);
                            $property = $objectPropertyCollection->getProperty($objectId, $fieldId); 
                            $element->setValue($property->getValue());
                        } else {
                            if (isset($pageData[$elementName])) {
                                $element->setValue($pageData[$elementName]);
                            }
                        }      
                    }
                }
            }            
        }
        
        return $form;
    }
}