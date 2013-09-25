<?php

namespace Users\FormFactory;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class UserFormFactory implements ServiceManagerAwareInterface
{
    protected $serviceManager;
    
    protected $objectTypeId;
    
    protected $userId;
    
    /**
     * GetForm() method will return form with data or not
     * @var boolean
     */
    protected $populateForm = true;
    
    protected $onlyVisible = false;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setObjectTypeId($objectTypeId)
    {
        $this->objectTypeId = $objectTypeId;
        return $this;
    }
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
    
    public function setPopulateForm($populateForm)
    {
        $this->populateForm = (bool)$populateForm;
        return $this;
    }
    
    public function setOnlyVisible($onlyVisible)
    {
        $this->onlyVisible = $onlyVisible;
        return $this;
    }
    
    public function getForm()
    {
        $formsMerger = $this->serviceManager->get('App\Form\FormsMerger');
        $formElementManager = $this->serviceManager->get('formElementManager');
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        $objectTypesCollection = $this->serviceManager->get('objectTypesCollection');
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $fieldsCollection = $this->serviceManager->get('fieldsCollection');
        
        $usersCollection = $this->serviceManager->get('Users\Collection\Users');
        
        $baseForm = $formElementManager->get('Users\Form\UserBaseForm', array(
            'userId' => $this->userId,
        ));
        
        $formsMerger->addForm($baseForm);
        
        $objectTypeId = $this->objectTypeId;
        
        if (null === $objectTypeId) {
            if (null !== $this->userId) {
                $user = $usersCollection->getUserById($this->userId);                
                $object = $objectsCollection->getObject($user->getObjectId());
                $objectTypeId = $object->getTypeId();
                $objectType = $objectTypesCollection->getType($objectTypeId);                 
                $formsMerger->addForm($objectType->getForm());
            }
        } else {                       
            $objectType = $objectTypesCollection->getType($objectTypeId);                    
            $formsMerger->addForm($objectType->getForm($this->onlyVisible));
        }
        
        $form = $formsMerger->getForm();
        
        if ($this->populateForm) {
            if (null === $this->userId) {
                $data = array(
                    'object_type_id' => $objectTypeId,
                );

                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($fieldset->getElements() as $element) {
                        $elName = $element->getName();
                        if ('field_' == substr($elName, 0, 6)) {
                            $fieldId = substr($elName, 6);
                            $field = $fieldsCollection->getField($fieldId);
                            if ('publish_date' == $field->getName()) {
                                $element->setValue(new \DateTime());
                            }
                        } else {
                            if (isset($data[$elName])) {
                                $element->setValue($data[$elName]);
                            }
                        }
                        
                    }
                }
            } else {     
                $user = $usersCollection->getUserById($this->userId);                
                $object = $objectsCollection->getObject($user->getObjectId());
                $objectId = $object->getId();
                
                $data = $user->toArray();     
                $data['object_type_id'] = $objectTypeId;
                unset($data['password']);
                
                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($fieldset->getElements() as $element) {
                        $elName = $element->getName();

                        if ('field_' == substr($elName, 0, 6)) {
                            $fieldId = substr($elName, 6);
                            $property = $objectPropertyCollection->getProperty($objectId, $fieldId);                         
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