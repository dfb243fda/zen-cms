<?php

namespace Rbac\Form;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Form\Form;

class RolesForm extends Form implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $table = 'roles';
    
    protected $roleId;
    
    /**
     * {@inheritDoc}
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
        return $this;
    }
    
    public function init()
    {
        $this->getFormFactory()->setFormElementManager($this->serviceManager->get('FormElementManager'));
        
        $db = $this->serviceManager->get('db');
        $translator = $this->serviceManager->get('translator');
        
        $query = 'select id, name from ' . DB_PREF . $this->table;
        $bind = array();
        
        if (null !== $this->roleId) {
            $query .= ' where id != ?';
            $bind[] = $this->roleId;
        }
        
        $sqlRes = $db->query($query, $bind)->toArray();
                
        $parentRoles = array(
            0 => '',
        );
        foreach ($sqlRes as $row) {
            $parentRoles[$row['id']] = $row['name'];
        }
                
        $this->add(array(
                'name' => 'name',
                'options' => array(
                    'label' => $translator->translate('Rbac:Name field'),
                ),
            ))
            ->add(array(
                'name' => 'parent',
                'type' => 'select',
                'options' => array(
                    'label' => $translator->translate('Rbac:Parent field'),
                    'value_options' => $parentRoles,
                ),
            ))->add(array(
                'name' => 'unauthorized',
                'type' => 'checkbox',
                'options' => array(
                    'label' => $translator->translate('Rbac:Unauthorized field'),
                    'value' => 1,
                ),
            ));
        
        $this->getInputFilter()->get('name')
                               ->setRequired(true)
                               ->getFilterChain()
                               ->attachByName('StringTrim');
    }
}