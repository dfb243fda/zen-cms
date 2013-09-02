<?php

namespace Installer\Form;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Form\Form;

class AccessForm extends Form implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    
    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function init()
    {        
        $this->getFormFactory()->setFormElementManager($this->serviceManager->get('formElementManager'));
        
        $translator = $this->serviceManager->get('translator');
        
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