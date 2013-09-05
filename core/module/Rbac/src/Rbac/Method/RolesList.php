<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;

class RolesList extends AbstractMethod
{
    public function main()
    {
        if ('get_data' == $this->params()->fromRoute('task')) {
            $rolesTree = $this->serviceLocator->get('Rbac\Service\RolesTree');
            
            $parentId = (int)$this->params()->fromPost('id', 0);
            $result = $rolesTree->getItems($parentId);
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/Rbac/roles_tree.phtml',
                ),
            );
        }
        return $result;
    }
    
    
    protected function getItems($parentId)
    {                
        $items = $this->rolesModel->getItems($parentId);
        
        foreach ($items as $k=>$row) {
            $items[$k]['icons'] = array();
            $items[$k]['icons']['editLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'Rbac',
                'method' => 'EditRole',
                'id' => $row['id']
            ));
            $items[$k]['icons']['addLink'] = $this->url()->fromRoute('admin/method', array(
                'module' => 'Rbac',
                'method' => 'AddRole',
                'id' => $row['id']
            ));
            $items[$k]['icons']['delLink'] = 'zen.roles.delRole(\'' . $this->url()->fromRoute('admin/method', array(
                'module' => 'Rbac',   
                'method' => 'DeleteRole',
            )) . '\', ' . $row['id'] . ')';            
        }
        
        return array(
            'items' => $items,
        );
    }
    
}