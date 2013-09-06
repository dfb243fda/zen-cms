<?php

namespace ContactForms\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;


class ContactForm extends Form implements ServiceLocatorAwareInterface
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
                'name' => 'name',
                'options' => array(
                    'label' => $translator->translate('Contact form name'),
                ),
            ))
            ->add(array(
                'name' => 'template',
                'type' => 'aceEditor',
                'options' => array(
                    'label' => $translator->translate('Contact form template'),
                ),
            ))
            ->add(array(
                'name' => 'recipient',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Recipient field'),
                ),
            ))
            ->add(array(
                'name' => 'sender',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Sender field'),
                ),
            ))
            ->add(array(
                'name' => 'subject',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Subject field'),
                ),
            ))
            ->add(array(
                'name' => 'mail_template',
                'type' => 'aceEditor',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Mail template field'),
                ),
            ))
            ->add(array(
                'name' => 'use_recipient2',
                'type' => 'checkbox',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Use recipient-2'),
                ),
            ))
            ->add(array(
                'name' => 'recipient2',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Recipient field'),
                ),
            ))
            ->add(array(
                'name' => 'sender2',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Sender field'),
                ),
            ))
            ->add(array(
                'name' => 'subject2',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Subject field'),
                ),
            ))
            ->add(array(
                'name' => 'mail_template2',
                'type' => 'aceEditor',
                'options' => array(
                    'label' => $translator->translate('ContactForms:Mail template field'),
                ),
            ));
        
        
        $this->getInputFilter()->get('name')->setRequired(true);
    }
    
}