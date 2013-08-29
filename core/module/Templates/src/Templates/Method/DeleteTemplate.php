<?php

namespace Templates\Method;

use App\Method\AbstractMethod;
use Templates\Model\Templates;

class DeleteTemplate extends AbstractMethod
{    
    public function main()
    {      
        $templatesModel = new Templates($this->serviceLocator, $this->url());
        
        if (!$this->params()->fromPost('id')) {
            throw new \Exception('Wrong parameters transferred');
        }
        
        $id = (int)$this->params()->fromPost('id');
        
        try {
            $success = $templatesModel->deleteTemplate($id);
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