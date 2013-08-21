<?php

namespace Rbac\Method;

use App\Method\AbstractMethod;
use Rbac\Model\Roles;
use Zend\Validator\AbstractValidator;

class AddRole extends AbstractMethod
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
        
        $parentRoleId = $this->params()->fromRoute('id' , 0);
        
        $form = $this->rolesModel->getForm(null, $parentRoleId);        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->rolesModel->add($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
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