<?php

namespace Installer\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class AccessForm extends Form implements ServiceLocatorAwareInterface
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
        $translator = $this->serviceLocator->getServiceLocator()->get('translator');
        
        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => $translator->translate('Installer email'),
                'description' => $translator->translate('Installer email description'),
            ),
        ));
        
        $this->add(array(
            'name' => 'password',
            'options' => array(
                'label' => $translator->translate('Installer password'),
                'description' => $translator->translate('Installer password description'),
            ),
            'attributes' => array(
                'autocomplete' => false,
            ),
        ));
        
        $this->getInputFilter()->get('email')->setRequired(true);
        $this->getInputFilter()->get('email')->getValidatorChain()
                                             ->attachByName('EmailAddress');
        $this->getInputFilter()->get('email')->getFilterChain()
                                             ->attachByName('StringTrim')
                                             ->attachByName('StringToLower');
        
        $this->getInputFilter()->get('password')->setRequired(true);
        $this->getInputFilter()->get('password')->getValidatorChain()
                                             ->attachByName('StringLength', array('min' => 6));
        $this->getInputFilter()->get('password')->getFilterChain()
                                             ->attachByName('StringTrim');
        
        
    }
}