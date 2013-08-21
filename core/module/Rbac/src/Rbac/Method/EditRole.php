<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Roles;
use Zend\Validator\AbstractValidator;

class EditRole extends AbstractMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->db = $this->rootServiceLocator->get('db');
        $this->rolesModel = new Roles($this->rootServiceLocator);     
        $this->request = $this->rootServiceLocator->get('request');
        
        AbstractValidator::setDefaultTranslator($this->rootServiceLocator->get('translator'));
    }

    public function main()
    {
        $result = array();
        
        $roleId = $this->params()->fromRoute('id');
        if (null === $roleId) {
            throw new \Exception('role id is undefined');
        }
        
        $form = $this->rolesModel->getForm($roleId);        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->rolesModel->edit($roleId, $this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
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