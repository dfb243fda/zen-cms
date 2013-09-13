<?php

namespace Users\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginzaConfigForm extends Form implements ServiceLocatorAwareInterface
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
            'type' => 'fieldset',
            'name' => 'loginza',
        ));
        
        $this->get('loginza')->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'domains',
            'options' => array(
                'count' => 2,
                'should_create_template' => true,
                'allow_add' => true,
                'target_element' => array(
                    'type' => 'Users\Fieldset\LoginzaFieldset'
                )
            )                     
        ));
    }
}