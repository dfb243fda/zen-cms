<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Permissions as PermissionsModel;

class Permissions extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $permissionsModel;
    
    protected $request;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->permissionsModel = new PermissionsModel($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {        
        if ($this->request->isPost()) {
            $result = array();
            
            if (null !== $this->request->getPost('role') &&
                null !== $this->request->getPost('resource') &&
                null !== $this->request->getPost('privelege') &&
                null !== $this->request->getPost('is_allowed')
                ) {
                $roleId = (int)$this->request->getPost('role');
                $resource = (string)$this->request->getPost('resource');
                $privelege = (string)$this->request->getPost('privelege');
                $isAllowed = (int)$this->request->getPost('is_allowed');
                
                $tmp = $this->permissionsModel->edit($roleId, $resource, $privelege, $isAllowed);
                
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
            $roles = $this->permissionsModel->getRoles();
            $permissions = $this->permissionsModel->getPermissions();
            
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/Rbac/permissions.phtml',
                    'data' => array(
                        'roles' => $roles,
                        'permissions' => $permissions,
                    ),
                ),
            );
        }
        
        return $result;
    }
}