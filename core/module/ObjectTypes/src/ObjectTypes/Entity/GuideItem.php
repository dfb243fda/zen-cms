<?php

namespace ObjectTypes\Entity;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class GuideItem implements ServiceManagerAwareInterface
{    
    protected $serviceManager;
    
    protected $guideItemId;
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setId($id)
    {
        $this->guideItemId = $id;
        return $this;
    }
    
    public function getForm()
    {
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $object = $objectsCollection->getObject($this->guideItemId);
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        
        $objectType = $object->getType();
        
        $form = $objectType->getForm(false, true);
            
        $data = array(
            'name' => $object->getName(),
        );

        $formData = array();                        
        foreach ($form->getFieldsets() as $fieldset) {
            $fieldsetName = $fieldset->getName();
            foreach ($fieldset->getElements() as $element) {
                $elementName = $element->getName();

                if ('field_' == substr($elementName, 0, 6)) {
                    $fieldId = substr($elementName, 6);
                    $property = $objectPropertyCollection->getProperty($this->guideItemId, $fieldId); 
                    $formData[$fieldsetName][$elementName] = $property->getValue();
                } else {
                    if (isset($data[$elementName])) {
                        $formData[$fieldsetName][$elementName] = $data[$elementName];
                    }
                }                    
            }
        }
        
        $form->setData($formData);
        
        return $form;
    }
    
    public function editGuideItem($data)
    {
        $objectsCollection = $this->serviceManager->get('objectsCollection');
        $object = $objectsCollection->getObject($this->guideItemId);
        $objectPropertyCollection = $this->serviceManager->get('objectPropertyCollection');
        
        $objectType = $object->getType();
        
        $insertFields = array();
        $insertBase = array();

        foreach ($data as $groupKey=>$groupData) {
            foreach ($groupData as $fieldName=>$fieldVal) {
                if ('field_' == substr($fieldName, 0, 6)) {
                    $insertFields[substr($fieldName, 6)] = $fieldVal;
                } else {
                    $insertBase[$fieldName] = $fieldVal;
                }
            }
        }

        $object->setName($insertBase['name'])->save();

        $tmpFieldGroups = $objectType->getFieldGroups();
        foreach ($tmpFieldGroups as $k=>$v) {
            $fields = $v->getFields();

            foreach ($fields as $k2=>$v2) {                    
                if (array_key_exists($k2, $insertFields)) {
                    $property = $objectPropertyCollection->getProperty($this->guideItemId, $k2); 
                    $property->setValue($insertFields[$k2])->save();
                }
            }
        }

        return true;
    }
    
}