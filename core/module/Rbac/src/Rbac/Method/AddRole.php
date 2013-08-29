<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Roles;

class AddRole extends AbstractMethod
{
    public function main()
    {
        $rolesModel = new Roles($this->serviceLocator);     
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        $parentRoleId = $this->params()->fromRoute('id' , 0);
        
        $form = $rolesModel->getForm(null, $parentRoleId);        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($request->isPost()) {
            $tmp = $rolesModel->add($this->params()->fromPost());
            if ($tmp['success']) {
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Роль успешно добавлена');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Rbac',
                        'method' => 'EditRole',
                        'id' => $tmp['roleId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Роль успешно добавлена',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Rbac/form_view.phtml',
            'data' => array(
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg' => $formMessages,
            ),
        );
        
        return $result;        
    }
}