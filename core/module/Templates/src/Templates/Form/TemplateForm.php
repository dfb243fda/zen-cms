<?php

namespace Templates\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class TemplateForm extends Form implements ServiceManagerAwareInterface
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
        $translator = $this->serviceManager->get('translator');
        
        $this->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
        
        $this->add(array(
                'name' => 'name',
                'options' => array(
                    'label' => $translator->translate('Templates:Template file'),
                ),
            ))
            ->add(array(
                'name' => 'title',
                'options' => array(
                    'label' => $translator->translate('Templates:Template name'),
                ),
            ))
            ->add(array(
                'type' => 'checkbox',
                'name' => 'is_default',
                'options' => array(
                    'label' => $translator->translate('Templates:Is default template'),
                ),
            ))
            ->add(array(
                'type' => 'aceEditor',
                'name' => 'content',
                'options' => array(
                    'label' => $translator->translate('Templates:Template content'),
                    'mode' => 'php',
                ),
            ))
            ->add(array(
                'type' => 'textarea',
                'name' => 'markers',
                'options' => array(
                    'label' => $translator->translate('Templates:Template markers'),
                ),
            ));
        
        $this->getInputFilter()
                ->get('name')
                ->setRequired(true);
                
        $this->getInputFilter()
                ->get('name')        
                ->getValidatorChain()
                ->attachByName('Regex', array('pattern' => '/.+\.phtml$/'));
        
        $this->getInputFilter()
                ->get('name')
                ->getFilterChain()
                ->attachByName('StringTrim')
                ->attachByName('StringToLower');
        
        $this->getInputFilter()
                ->get('title')
                ->setRequired(true)
                ->getFilterChain()
                ->attachByName('StringTrim');
    }
    
}