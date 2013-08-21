<?php

namespace Rbac\Model;

use Zend\Form\Factory;

class Roles
{
    protected $table = 'roles';
    protected $userRoleLinkerTable = 'user_role_linker';
    protected $rolePermissionsTable = 'role_permissions';
    
    public function __construct($sm)
    {
        $this->serviceManager = $sm;
        $this->db = $sm->get('db');
        $this->translator = $sm->get('translator');
        $this->table = DB_PREF . $this->table;
        $this->userRoleLinkerTable = DB_PREF . $this->userRoleLinkerTable;
        $this->rolePermissionsTable = DB_PREF . $this->rolePermissionsTable;
    }
    
    public function getItems($parentId)
    {
        $items = array();    
        
        $sqlRes = $this->db->query('select t1.*, (select count(t2.id) from ' . $this->table . ' t2 where t2.parent = t1.id) AS children_cnt from ' . $this->table . ' t1 where t1.parent = ?', array($parentId))->toArray();
        
        foreach ($sqlRes as $row) {
            if ($row['children_cnt'] > 0) {
                $row['state'] = 'closed';
            } else {
                $row['state'] = 'open';
            }            
            
            $items[] = $row;
        }
        
        return $items;
    }
    
    public function getForm($roleId = null, $parentRoleId = 0)
    {
        $query = 'select id, name from ' . $this->table;
        $bind = array();
        
        if (null !== $roleId) {
            $query .= ' where id != ?';
            $bind[] = $roleId;
        }
        
        $sqlRes = $this->db->query($query, $bind)->toArray();
        
        $parentRoles = array(
            0 => '',
        );
        foreach ($sqlRes as $row) {
            $parentRoles[$row['id']] = $row['name'];
        }
        
        if (null === $roleId) {
            $formValues = array(
                'parent' => $parentRoleId
            );
        } else {           
            $sqlRes = $this->db->query('select * from ' . $this->table . ' where id = ?', array($roleId))->toArray();
            if (empty($sqlRes)) {
                throw new \Exception('not found role ' . $roleId);
            }
            $formValues = $sqlRes[0];
        }
        
        
        $formConfig = array(
            'elements' => array(
                array(
                    'spec' => array(
                        'name' => 'name',
                        'options' => array(
                            'label' => $this->translator->translate('Rbac:Name field'),
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'parent',
                        'type' => 'select',
                        'options' => array(
                            'label' => $this->translator->translate('Rbac:Parent field'),
                            'value_options' => $parentRoles,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name' => 'unauthorized',
                        'type' => 'checkbox',
                        'options' => array(
                            'label' => $this->translator->translate('Rbac:Unauthorized field'),
                            'value' => 1,
                        ),
                    ),
                ),
            ),
            'input_filter' => array(
                'name' => array(
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),
                    )
                ),
            ),
        );
        
        return array(
            'formConfig' => $formConfig,
            'formValues' => $formValues,
        );        
    }
    
    public function add($data)
    {
        $tmp = $this->getForm();
        $formConfig = $tmp['formConfig'];  
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);   
        
        $form->setData($data);
        
        if ($form->isValid()) { 
            $data = $form->getData();
                
            $sqlRes = $this->db->query('select max(sorting) as max_sorting from ' . $this->table, array())->toArray();
            
            $maxSorting = $sqlRes[0]['max_sorting'];
            if (null === $maxSorting) {
                $maxSorting = 0;
            }
            $sorting = $maxSorting + 1;
            
            $this->db->query('
                insert into ' . $this->table . ' (name, parent, unauthorized, sorting)
                values (?, ?, ?, ?)
            ', array($data['name'], $data['parent'], $data['unauthorized'], $sorting));
            
            $result['roleId'] = $this->db->getDriver()->getLastGeneratedValue();
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
    }
    
    public function edit($roleId, $data)
    {
        $tmp = $this->getForm($roleId);
        $formConfig = $tmp['formConfig'];  
        
        $factory = new Factory($this->serviceManager->get('FormElementManager'));            
        $form = $factory->createForm($formConfig);   
        
        $form->setData($data);
        
        if ($form->isValid()) { 
            $data = $form->getData();
                        
            $this->db->query('
                update ' . $this->table . '
                    set name = ?, parent = ?, unauthorized = ?
                where id = ?
            ', array($data['name'], $data['parent'], $data['unauthorized'], $roleId));
            
            $result['success'] = true; 
        } else {
            $result['success'] = false;            
        }
        $result['form'] = $form;
        
        return $result;  
    }
    
    public function delete($roleId)
    {
        $this->db->query('
            delete from ' . $this->userRoleLinkerTable . '
            where role_id = ?
        ', array($roleId));
        
        $this->db->query('
            delete from ' . $this->rolePermissionsTable . '
            where role = ?
        ', array($roleId));
        
        $this->db->query('
                delete from ' . $this->table . '
                where id = ?
            ', array($roleId));
        
        return true;
    }
}