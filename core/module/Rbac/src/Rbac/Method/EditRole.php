<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Roles;

class EditRole extends AbstractMethod
{
    public function main()
    {
        $rolesModel = new Roles($this->serviceLocator);     
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        $roleId = $this->params()->fromRoute('id');
        if (null === $roleId) {
            throw new \Exception('role id is undefined');
        }
        
        $form = $rolesModel->getForm($roleId);        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($request->isPost()) {
            $tmp = $rolesModel->edit($roleId, $this->params()->fromPost());
            if ($tmp['success']) {
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Роль успешно изменена');
                    $this->redirect()->refresh();
                }

                return array(
                    'success' => true,
                    'msg' => 'Роль успешно изменена',
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
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
        return $result;        
    }
}