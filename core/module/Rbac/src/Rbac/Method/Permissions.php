<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Permissions as PermissionsModel;

class Permissions extends AbstractMethod
{    
    public function main()
    {        
        $permissionsCollection = $this->serviceLocator->get('Rbac\Collection\Permissions');
        $rolesCollection = $this->serviceLocator->get('Rbac\Collection\Roles');
        
        $request = $this->serviceLocator->get('request');
        
        if ($request->isPost()) {
            $result = array();
            
            if (null !== $this->params()->fromPost('role') &&
                null !== $this->params()->fromPost('resource') &&
                null !== $this->params()->fromPost('privelege') &&
                null !== $this->params()->fromPost('is_allowed')
                ) {
                $roleId = (int)$this->params()->fromPost('role');
                $resource = (string)$this->params()->fromPost('resource');
                $privelege = (string)$this->params()->fromPost('privelege');
                $isAllowed = (int)$this->params()->fromPost('is_allowed');
                
                $tmp = $permissionsCollection->edit($roleId, $resource, $privelege, $isAllowed);
                
                if ($tmp['success']) {
                    $result['success'] = true;
                    $result['msg'] = 'Привилегии успешно обновлены';
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = $tmp['errMsg'];
                }
                
            } else {
                $result['success'] = false;
                $result['errMsg'] = 'Переданы неверные параметры';
            }
            
        } else {
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/Rbac/permissions.phtml',
                    'data' => array(
                        'roles' => $rolesCollection->getRoles(),
                        'permissions' => $permissionsCollection->getPermissions(),
                    ),
                ),
            );
        }
        
        return $result;
    }
}