<?php

namespace Rbac\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Form;

class RolesForm extends Form implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    protected $table = 'roles';
            
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
    
        $roleId = $this->getOption('roleId');
        
        $query = 'select id, name from ' . DB_PREF . $this->table;
        $bind = array();
        
        if (null !== $roleId) {
            $query .= ' where id != ?';
            $bind[] = $roleId;
        }
        
        $sqlRes = $db->query($query, $bind)->toArray();
                
        $parentRoles = array(
            0 => '',
        );
        foreach ($sqlRes as $row) {
            $parentRoles[$row['id']] = $translator->translateI18n($row['name']);
        }
                
        $this->add(array(
                'name' => 'name',
                'options' => array(
                    'label' => 'Rbac:Name field',
                ),
            ))
            ->add(array(
                'name' => 'parent',
                'type' => 'select',
                'options' => array(
                    'label' => 'Rbac:Parent field',
                    'value_options' => $parentRoles,
                ),
            ))->add(array(
                'name' => 'unauthorized',
                'type' => 'checkbox',
                'options' => array(
                    'label' => 'Rbac:Unauthorized field',
                    'value' => 1,
                ),
            ));
        
        $this->getInputFilter()->get('name')
                               ->setRequired(true)
                               ->getFilterChain()
                               ->attachByName('StringTrim');
    }
}