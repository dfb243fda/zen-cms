<?php

namespace Rbac\FormFactory;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class RolesFormFactory implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    protected $roleId;
    
    protected $parentRoleId = 0;
    
    protected $table = 'roles';
    
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
    
    public function setParentRoleId($parentRoleId)
    {
        $this->parentRoleId = $parentRoleId;
        return $this;
    }        
    
    public function getForm()
    {
        $form = $this->serviceManager->get('Rbac\Form\RolesForm');
        
        $form->setRoleId($this->roleId)->init();
        
        if (null !== $this->roleId) {
            $db = $this->serviceManager->get('db');
            
            $sqlRes = $db->query('
                select * 
                from ' . DB_PREF . $this->table . ' 
                where id = ?', array($this->roleId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('not found role ' . $roleId);
            }
            $formValues = $sqlRes[0];
        } else {
            $formValues = array(
                'parent' => $this->parentRoleId,
            );
        }
        
        $form->setData($formValues);
        
        return $form;
    }
    
}