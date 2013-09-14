<?php

namespace ObjectTypes\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FieldsGroupAdminForm extends Form implements ServiceLocatorAwareInterface
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
        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => 'ObjectTypes:Group name field',
            ),
        ));
        
        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'ObjectTypes:Group identifier field',
            ),
        ));        
        
        $this->getInputFilter()->get('title')->setRequired(true);
        
        $this->getInputFilter()->get('name')->setRequired(true);
    }
}