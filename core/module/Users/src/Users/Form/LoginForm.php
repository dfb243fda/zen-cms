<?php

namespace Users\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class LoginForm extends Form implements ServiceLocatorAwareInterface
{
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
        $config = $this->serviceLocator->getServiceLocator()->get('config');
        $translator = $this->serviceLocator->getServiceLocator()->get('translator');
        
        $usersConfig = $config['Users'];  
        
        $identityLabel = 'Users:Identity field:' . implode(' or ', $usersConfig['authIdentityFields']);
        
        $identityFields = $usersConfig['authIdentityFields'];
        
        $this->add(array(
                'name' => 'identity',
                'options' => array(
                    'label' => $translator->translate($identityLabel),
                ),
                'attributes' => array(
                    'autofocus' => 'autofocus',
                ),
            ))
            ->add(array(
                'name' => 'credential',
                'type' => 'password',
                'options' => array(
                    'label' => $translator->translate('Users:Credential field'),
                ),
            ));
        
        
        $this->getInputFilter()->get('identity')
                               ->setRequired(true);
        
        if ($identityFields == array('email')) {
            $this->getInputFilter()->get('identity')
                                   ->getValidatorChain()
                                   ->attachByName('EmailAddress');
        }
        
        $this->getInputFilter()->get('credential')
                               ->setRequired(true);
        
        $this->getInputFilter()->get('credential')
                               ->getValidatorChain()
                               ->attachByName('StringLength', array('min' => 6));
        
        $this->getInputFilter()->get('credential')
                               ->getFilterChain()
                               ->attachByName('StringTrim');
    }
}