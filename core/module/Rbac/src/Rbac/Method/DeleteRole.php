<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Roles;

class DeleteRole extends AbstractMethod
{
    public function main()
    {
        $rolesModel = new Roles($this->serviceLocator);     
        
        $result = array(
            'success' => false,
        );
        
        $roleId = $this->params()->fromPost('id');
        if (null === $roleId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $roleId = (int)$roleId;
        
        if ($rolesModel->delete($roleId)) {
            $result['success'] = true;
            $result['msg'] = 'Роль успешно удалена';
        } else {
            $result['success'] = false;
            $result['msg'] = 'Не удалось удалить роль';
        }
        
        return $result;        
    }
}