<?php

namespace ContactForms\Method;

use App\Method\AbstractMethod;
use ContactForms\Model\Forms as FormsModel;

class DelForm extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $formsModel;
    
    protected $db;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        
        $this->formsModel = new FormsModel($this->rootServiceLocator);
        
        $this->db = $this->rootServiceLocator->get('db');
        
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {
        $result = array(
            'success' => false,
        );
        
        $formId = $this->request->getPost('id');
        if (null === $formId) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $formId = (int)$formId;
        
        if ($this->formsModel->delContactForm($formId)) {
            $result['success'] = true;
            $result['msg'] = 'Форма успешно удалена';
        } else {
            $result['success'] = false;
            $result['msg'] = 'Не удалось удалить форму';
        }
        
        return $result;        
    }
}