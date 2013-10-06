<?php

namespace Rbac\Service;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Form\Form;

class DynamicConfig implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getConfig()
    {
        $db = $this->serviceManager->get('db');
        
        $sqlRes = $db->query('select id, name from ' . DB_PREF . 'roles', array())->toArray();
        
        $roles = array();
        foreach ($sqlRes as $row) {
            $roles[$row['id']] = $row['name'];
        }
        
        $form = new Form();
        $form->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
        
        $form->add(array(
            'name' => 'users',
            'type' => 'fieldset',
        ));
        
        $form->get('users')->add(array(
            'type' => 'select',
            'name' => 'new_user_roles',
            'options' => array(
                'label' => 'Dynamic config new user roles',
                'description' => 'Dynamic config new user roles description',
                'value_options' => $roles,
            ),
            'attributes' => array(
                'multiple' => true,
            ),
        ));
        
        return array(
            'form' => array(
                'registration' => $form,
            ),            
        );     
    }
}