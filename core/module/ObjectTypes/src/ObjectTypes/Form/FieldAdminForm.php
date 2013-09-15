<?php

namespace ObjectTypes\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FieldAdminForm extends Form implements ServiceLocatorAwareInterface
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
        $fieldsGroup = $this->getOption('fieldsGroup');        
        
        $rootServiceManager = $this->serviceLocator->getServiceLocator();
        
        $translator = $rootServiceManager->get('translator');
        $objectTypesCollection = $rootServiceManager->get('objectTypesCollection');
        $fieldTypesCollection = $rootServiceManager->get('fieldTypesCollection');
        
        $guides = $objectTypesCollection->getGuidesList();

        $fieldTypes = $fieldTypesCollection->getFieldTypes();
        foreach ($fieldTypes as $k => $v) {
            $fieldTypes[$k] = $v->getTitle();
        }
        
        
        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => 'ObjectTypes:Field name field',
            ),
        ));
        
        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'ObjectTypes:Field identifier field',
            ),
        ));
        
        $this->add(array(
            'name' => 'tip',
            'options' => array(
                'label' => 'ObjectTypes:Field tip field',
            ),
        ));
        
        $this->add(array(
            'type' => 'select',
            'name' => 'field_type_id',
            'options' => array(
                'label' => 'ObjectTypes:Field type field',
                'value_options' => $fieldTypes,
            ),
        ));
        
        $this->add(array(
            'type' => 'select',
            'name' => 'guide_id',
            'options' => array(
                'label' => 'ObjectTypes:Field guide field',
                'empty_option' => '',
                'value_options' => $guides,
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'is_required',
            'options' => array(
                'label' => 'ObjectTypes:Field is required field',
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'is_visible',
            'options' => array(
                'label' => 'ObjectTypes:Field is visible field',
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'in_filter',
            'options' => array(
                'label' => 'ObjectTypes:Field in filter field',
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'in_search',
            'options' => array(
                'label' => 'ObjectTypes:Field in search field',
            ),
        ));
        
        $this->getInputFilter()->get('title')->setRequired(true);
        $this->getInputFilter()->get('name')->setRequired(true);
        
        if ($fieldsGroup = $this->getOption('fieldsGroup')) {            
            $this->getInputFilter()->get('name')
                                   ->getValidatorChain()
                                   ->attachByName('ObjectTypes\Validator\NoFieldWithSuchNameExists', array(
                                       'fieldsGroup' => $fieldsGroup,
                                   ));
        }
        
        
        $this->getInputFilter()->get('guide_id')->setRequired(false);

    }
}