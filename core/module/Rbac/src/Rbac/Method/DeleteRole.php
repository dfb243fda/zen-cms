<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;

class DeleteRole extends AbstractMethod
{
    public function main()
    {
        $rolesCollection = $this->serviceLocator->get('Rbac\Collection\Roles');     
        
        $result = array(
            'success' => false,
        );
        
        $roleId = $this->params()->fromPost('id');
        if (null === $roleId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $roleId = (int)$roleId;
        
        if ($rolesCollection->deleteRole($roleId)) {
            $result['success'] = true;
            $result['msg'] = 'Роль успешно удалена';
        } else {
            $result['success'] = false;
            $result['msg'] = 'Не удалось удалить роль';
        }
        
        return $result;        
    }
}