<?php

namespace Installer\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class DbSettingsForm extends Form implements ServiceLocatorAwareInterface
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
            'name' => 'dbname',
            'options' => array(
                'label' => $translator->translate('Installer database name'),
                'description' => $translator->translate('Installer database name description'),
            ),
        ));
        
        $this->add(array(
            'name' => 'dbuser',
            'options' => array(
                'label' => $translator->translate('Installer database user'),
                'description' => $translator->translate('Installer database user description'),
            ),
        ));
        
        $this->add(array(
            'name' => 'dbpass',
            'options' => array(
                'label' => $translator->translate('Installer database password'),
                'description' => $translator->translate('Installer database password description'),
            ),
            'attributes' => array(
                'autocomplete' => false,
            ),
        ));
        
        $this->add(array(
            'name' => 'dbaddr',
            'options' => array(
                'label' => $translator->translate('Installer database server address'),
                'description' => $translator->translate('Installer database server address description'),
            ),
        ));
        
        $this->add(array(
            'name' => 'dbpref',
            'options' => array(
                'label' => $translator->translate('Installer tables prefix'),
                'description' => $translator->translate('Installer tables prefix description'),
            ),
        ));
        
        $this->getInputFilter()->get('dbname')->setRequired(true);
        $this->getInputFilter()->get('dbuser')->setRequired(true);
        $this->getInputFilter()->get('dbpass')->setRequired(true);
        $this->getInputFilter()->get('dbaddr')->setRequired(true);
    }
}