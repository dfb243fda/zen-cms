<?php

namespace Templates\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TemplateForm extends Form implements ServiceLocatorAwareInterface
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
                'name' => 'title',
                'options' => array(
                    'label' => $translator->translate('Templates:Template name'),
                ),
            ))
            ->add(array(
                'name' => 'name',
                'options' => array(
                    'label' => $translator->translate('Templates:Template file'),
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
            ));
/*        
        $this->getInputFilter()
                ->get('name')
                ->setRequired(true);
                
        $this->getInputFilter()
                ->get('name')        
                ->getValidatorChain()
                ->attachByName('Regex', array('pattern' => '/^[a-z_\-0-9]+\.[a-z]{3,4}$/'));
        
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
 * 
 */
    }
    
}