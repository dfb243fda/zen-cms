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
    
}