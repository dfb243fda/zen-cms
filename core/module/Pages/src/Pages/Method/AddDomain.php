<?php

namespace Pages\Method;

use App\Method\AbstractMethod;
use Pages\Model\Domains;

class AddDomain extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $domainsModel;
    
    protected $request;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->domainsModel = new Domains($this->rootServiceLocator);        
        $this->request = $this->rootServiceLocator->get('request');
    }

    public function main()
    {
        $result = array();
                       
        $form = $this->domainsModel->getForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->domainsModel->add($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Домен успешно обновлен');
                    $this->redirect()->toRoute('admin/method', array(
                        'module' => 'Pages',
                        'method' => 'EditDomain',
                        'id'     => $tmp['domainId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Домен успешно добавлен',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/domain_form_view.phtml',
            'data' => array(
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg' => $formMessages,
            ),
        );
        
        return $result;  
    }
}