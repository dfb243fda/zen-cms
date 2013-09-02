<?php

namespace Installer\Form;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Form\Form;

class LanguageForm extends Form implements ServiceManagerAwareInterface
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