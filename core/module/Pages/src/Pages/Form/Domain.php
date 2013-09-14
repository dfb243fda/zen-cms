<?php

namespace Pages\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Domain extends Form implements ServiceLocatorAwareInterface
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
        $db = $this->serviceLocator->getServiceLocator()->get('db');
        $translator = $this->serviceLocator->getServiceLocator()->get('translator');

        $sqlRes = $db->query('select id, title from ' . DB_PREF . 'langs', array())->toArray();
        $langs = array();
        foreach ($sqlRes as $row) {
            $langs[$row['id']] = $row['title'];
        }


        $this->add(array(
                'name' => 'host',
                'options' => array(
                    'label' => 'Pages:Domain host field',
                ),
            ))
            ->add(array(
                'type' => 'checkbox',
                'name' => 'is_default',
                'options' => array(
                    'label' => 'Pages:Domain is default field',
                    'value' => 1,
                ),
            ))
            ->add(array(
                'type' => 'select',
                'name' => 'default_lang_id',
                'options' => array(
                    'label' => 'Pages:Domain default lang field',
                    'value_options' => $langs,
                ),
            ))
            ->add(array(
                'type' => 'textarea',
                'name' => 'domain_mirrors',
                'options' => array(
                    'label' => 'Pages:Domain mirrors field',
                ),
            ));
        
        $this->getInputFilter()
             ->get('host')
             ->setRequired(true)
             ->getFilterChain()
             ->attachByName('StringTrim');
        
        $this->getInputFilter()
             ->get('default_lang_id')
             ->setRequired(true);  
    }
}