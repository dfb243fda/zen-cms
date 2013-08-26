<?php

namespace ContactForms\Form;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Form\Form;


class ContactForm extends Form implements ServiceManagerAwareInterface
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