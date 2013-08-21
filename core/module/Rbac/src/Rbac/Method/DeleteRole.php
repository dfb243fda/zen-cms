<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Roles;

class DeleteRole extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->rolesModel = new Roles($this->rootServiceLocator);     
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array(
            'success' => false,
        );
        
        $roleId = $this->request->getPost('id');
        if (null === $roleId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $roleId = (int)$roleId;
        
        if ($this->rolesModel->delete($roleId)) {
            $result['success'] = true;
            $result['msg'] = 'Роль успешно удалена';
        } else {
            $result['success'] = false;
            $result['msg'] = 'Не удалось удалить роль';
        }
        
        return $result;        
    }
}