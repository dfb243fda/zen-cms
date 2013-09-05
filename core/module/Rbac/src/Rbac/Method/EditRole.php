<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;

class EditRole extends AbstractMethod
{
    public function main()
    {   
        $rolesFormFactory = $this->serviceLocator->get('Rbac\FormFactory\RolesFormFactory');
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        $roleId = $this->params()->fromRoute('id');
        if (null === $roleId) {
            throw new \Exception('role id is undefined');
        }
        
        $rolesFormFactory->setRoleId($roleId);
        
        $form = $rolesFormFactory->getForm();   
        
        if ($request->isPost()) {
            $roleEntity = $this->serviceLocator->get('Rbac\Entity\RoleEntity');
            
            $roleEntity->setRoleId($roleId);
            
            if ($roleEntity->edit($this->params()->fromPost())) {
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Роль успешно изменена');
                    return $this->redirect()->refresh();
                }

                return array(
                    'success' => true,
                    'msg' => 'Роль успешно изменена',
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
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
        return $result;        
    }
}