<?php

namespace Installer\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class LanguageForm extends Form implements ServiceLocatorAwareInterface
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
            'name' => 'language',
            'type' => 'select',
            'options' => array(
                'label' => $translator->translate('Installer language'),
                'description' => $translator->translate('Installer language description'),
                'value_options' => array(
                    'ru_RU' => 'Русский',
                    'en_EN' => 'Английский',
                ),
            ),
        ));
    }
}