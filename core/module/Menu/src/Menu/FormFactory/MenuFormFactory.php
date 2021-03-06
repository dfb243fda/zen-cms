<?php

namespace Menu\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class MenuFormFactory implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $objectId;
    
    /**
     * GetForm() method will return form with data or not
     * @var boolean
     */
    protected $populateForm = true;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }
    
    public function setPopulateForm($populateForm)
    {
        $this->populateForm = (bool)$populateForm;
        return $this;
    }
    
    public function getForm()
    {
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $formsMerger = $this->serviceManager->get('App\Form\FormsMerger');
        $menuService = $this->serviceManager->get('Menu\Service\Menu');
        
        $baseForm = $this->serviceManager->get('FormElementManager')
                                     ->get('Menu\Form\BaseMenuForm');  
        
        $formsMerger->addForm($baseForm);
                
        $objectTypeId = $this->objectTypeId;
        
        if (null === $objectTypeId) {
            if (null === $this->objectId) {
                $objectTypeId = $objectTypesCollection->getTypeIdByGuid($menuService->getMenuGuid());  
            } else {
                $object = $objectsCollection->getObject($this->objectId);
                $objectTypeId = $object->getTypeId();
            }
        }
        $objectType = $objectTypesCollection->getType($objectTypeId);  
        $formsMerger->addForm($objectType->getForm());
        
        $form = $formsMerger->getForm();
        
        if ($this->populateForm) {
            if (null === $this->objectId) {
                $data = array(
                    'type_id' => $objectTypeId,
                );

                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($fieldset->getElements() as $element) {
                        $elName = $element->getName();
                        if (isset($data[$elName])) {
                            $element->setValue($data[$elName]);
                        }
                    }
                }
            } else {            
                $object = $objectsCollection->getObject($this->objectId);
                $data = array(
                    'name' => $object->getName(),
                    'type_id' => $objectTypeId,
                );

                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($fieldset->getElements() as $element) {
                        $elName = $element->getName();

                        if ('field_' == substr($elName, 0, 6)) {
                            $fieldId = substr($elName, 6);
                            $property = $objectPropertyCollection->getProperty($this->objectId, $fieldId);                         
                            $element->setValue($property->getValue());
                        } else {
                            if (isset($data[$elName])) {
                                $element->setValue($data[$elName]);
                            }
                        }

                    }
                }
            }
        }
        
        return $form;
    }
}