<?php

namespace Users\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class LoginzaFieldset extends Fieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface
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
        $this->setLabel('Users:Loginza settings for domain');
        
//        $this->setUseAsBaseFieldset(true);
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'allow_loginza',
            'options' => array(
                'label' => 'Dynamic config allow loginza',
                'description' => 'i18n::Dynamic config allow loginza description',
            ),
        ));
                
        $request = $this->serviceLocator->getServiceLocator()->get('request');
        $host = $request->getUri()->getHost() . $request->getBasePath();
        $translator = $this->serviceLocator->getServiceLocator()->get('translator');
        
        $this->add(array(
            'name' => 'domain',
            'options' => array(
                'label' => 'Dynamic config loginza domain',
                'description' => sprintf($translator->translate('Dynamic config loginza domain description %s'), $host),
            ),
        ));
        
        $this->add(array(
            'name' => 'widget_id',
            'options' => array(
                'label' => 'Dynamic config loginza_widget_id',
                'description' => 'i18n::Dynamic config loginza_widget_id description',
            ),
        ));
        
        $this->add(array(
            'name' => 'secret',
            'options' => array(
                'label' => 'Dynamic config loginza_secret',
                'description' => 'i18n::Dynamic config loginza_secret description',
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'secret_is_protected',
            'options' => array(
                'label' => 'Dynamic config loginza_secret_is_protected',
                'description' => 'i18n::Dynamic config loginza_secret_is_protected description',
            ),
        ));
    }
   
    public function getInputFilterSpecification()
    {        
        if ($this->get('secret_is_protected')->isChecked()) {
            $required = true;
        } else {
            $required = false;
        }
        
        return array(
            'domain' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StringTrim',)
                )
            ),
            'widget_id' => array(
                'required' => $required,
                'filters' => array(
                    array('name' => 'StringTrim',)
                )
            ),
            'secret' => array(
                'required' => $required,
                'filters' => array(
                    array('name' => 'StringTrim',)
                )
            ),
        );
    }
}