<?php

namespace Templates\Method;

use App\Method\AbstractMethod;

class DeleteTemplate extends AbstractMethod
{    
    public function main()
    {      
        if (!$this->params()->fromPost('id')) {
            throw new \Exception('Wrong parameters transferred');
        }
        
        $templatesCollection = $this->serviceLocator->get('Templates\Collection\TemplatesCollection');
        
        $id = (int)$this->params()->fromPost('id');
        
        $success = false;
        try {
            if ($templatesCollection->deleteTemplate($id)) {
                $success = true;
            }
        } catch (\Exception $e) {}
        
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