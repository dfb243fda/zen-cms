<?php

namespace Users\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DynamicConfigForm extends Form implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function init()
    {
        $objectTypesCollection = $this->serviceLocator->getServiceLocator()->get('objectTypesCollection');
        
        $guid = 'user-item';
        $parentId = $objectTypesCollection->getTypeIdByGuid($guid);
        
        $descendantTypeIds = $objectTypesCollection->getDescendantTypeIds($parentId);
        
        $typeIds = array_merge(array($parentId), $descendantTypeIds);
        
        $typeOptions = array();
        foreach ($typeIds as $typeId) {
            $objectType = $objectTypesCollection->getType($typeId);
            $typeOptions[$typeId] = $objectType->getName();
        }
        
        $this->add(array(
            'type' => 'fieldset',
            'name' => 'users',
        ));
        
        $this->get('users')->add(array(
            'type' => 'select',
            'name' => 'new_user_object_type',
            'options' => array(
                'label' => 'i18n::Users:Dynamic config new object type',
                'description' => 'i18n::Users:Dynamic config new object type description',
                'value_options' => $typeOptions,
            ),
        ));
        
        $this->getInputFilter()
             ->get('registration')
             ->get('users')
             ->setRequired(true);
    }
}