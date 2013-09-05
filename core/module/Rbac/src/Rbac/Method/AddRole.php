<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;

class AddRole extends AbstractMethod
{
    public function main()
    {
        $rolesFormFactory = $this->serviceLocator->get('Rbac\FormFactory\RolesFormFactory');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        $parentRoleId = $this->params()->fromRoute('id' , 0);
        
        $rolesFormFactory->setParentRoleId($parentRoleId);
        
        $form = $rolesFormFactory->getForm();     
        
        if ($request->isPost()) {
            $rolesCollection = $this->serviceLocator->get('Rbac\Collection\Roles');
            
            if ($roleId = $rolesCollection->addRole($this->params()->fromPost())) {
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Роль успешно добавлена');
                    return $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Rbac',
                        'method' => 'EditRole',
                        'id' => $roleId,
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Роль успешно добавлена',
                );         
            } else {
                $result['success'] = false;
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Rbac/form_view.phtml',
            'data' => array(
                'form' => $form,
            ),
        );
        
        return $result;        
    }
}