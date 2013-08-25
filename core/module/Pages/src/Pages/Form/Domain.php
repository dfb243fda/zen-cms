<?php

namespace Pages\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Domain extends Form implements ServiceManagerAwareInterface
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
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');

        $sqlRes = $db->query('select id, title from ' . DB_PREF . 'langs', array())->toArray();
        $langs = array();
        foreach ($sqlRes as $row) {
            $langs[$row['id']] = $row['title'];
        }


        $this->add(array(
                'name' => 'host',
                'options' => array(
                    'label' => $translator->translate('Pages:Domain host field'),
                ),
            ))
            ->add(array(
                'type' => 'checkbox',
                'name' => 'is_default',
                'options' => array(
                    'label' => $translator->translate('Pages:Domain is default field'),
                    'value' => 1,
                ),
            ))
            ->add(array(
                'type' => 'select',
                'name' => 'default_lang_id',
                'options' => array(
                    'label' => $translator->translate('Pages:Domain default lang field'),
                    'value_options' => $langs,
                ),
            ))
            ->add(array(
                'type' => 'textarea',
                'name' => 'domain_mirrors',
                'options' => array(
                    'label' => $translator->translate('Pages:Domain mirrors field'),
                ),
            ));
        
        $this->getInputFilter()
             ->get('host')
             ->setRequired(true)
             ->getFilterChain()
             ->attachByName('Zend\Filter\StringTrim');
        
        $this->getInputFilter()
             ->get('default_lang_id')
             ->setRequired(true);  
    }
}