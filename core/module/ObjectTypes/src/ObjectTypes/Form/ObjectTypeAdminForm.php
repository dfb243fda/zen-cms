<?php

namespace ObjectTypes\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ObjectTypeAdminForm extends Form implements ServiceLocatorAwareInterface
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
        
        $sqlRes = $db->query('
            select id, title 
            from ' . DB_PREF . 'page_types', array())->toArray();

        $pageTypes = array(
            '0' => '',
        );
        foreach ($sqlRes as $row) {
            $pageTypes[$row['id']] = $translator->translateI18n($row['title']);
        }

        $sqlRes = $db->query('
            select id, title 
            from ' . DB_PREF . 'page_content_types', array())->toArray();

        $pageContentTypes = array(
            '0' => '',
        );
        foreach ($sqlRes as $row) {
            $pageContentTypes[$row['id']] = $translator->translateI18n($row['title']);
        }

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'ObjectTypes:Object type name field',
            ),
            'attributes' => array(
                'type' => 'text',
            ),
        ));
        
        $this->add(array(
            'type' => 'select',
            'name' => 'page_type_id',
            'options' => array(
                'label' => 'ObjectTypes:Page type field',
                'value_options' => $pageTypes,
            ),
        ));
        
        $this->add(array(
            'type' => 'select',
            'name' => 'page_content_type_id',
            'options' => array(
                'label' => 'ObjectTypes:Page content type field',
                'value_options' => $pageContentTypes,
            ),
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name' => 'is_guidable',
            'options' => array(
                'label' => 'ObjectTypes:Is guidable field',
            ),
        ));
        
        $this->getInputFilter()->get('name')->setRequired(true);
    }
}