<?php

namespace Templates\Method;

use App\Method\AbstractMethod;
use Templates\Model\Templates;

class DeleteTemplate extends AbstractMethod
{
    protected $templatesModel;    
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->templatesModel = new Templates($this->rootServiceLocator, $this->url());
        $this->moduleManager = $this->rootServiceLocator->get('moduleManager');
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {      
        if (!$this->request->getPost('id')) {
            throw new \Exception('Wrong parameters transferred');
        }
        
        $id = (int)$this->request->getPost('id');
        
        try {
            $success = $this->templatesModel->deleteTemplate($id);
        } catch (\Exception $e) {
            $success = false;
        }
        
        
        $result = array(
            'success' => $success,
        );
        
        if ($success) {
            $result['msg'] = 'Шаблон успешно удален';
        } else {
            $result['errMsg'] = 'Во время удаления шаблона произошли ошибки, возможно с шаблоном связаны существующие страницы';
        }
        
        return $result;
    }
}