<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class DeletePage extends AbstractMethod
{    
    public function main()
    {
        $result = array(
            'success' => false,
        );
                
        if (null === $this->params()->fromPost('id')) {
            $result['errMsg'] = 'Не передан параметр id';
            return $result;
        }
        $pageId = (int)$this->params()->fromPost('id');
        
        $pagesCollection = $this->serviceLocator->get('Pages\Collection\Pages');
        
        if ($pagesCollection->deletePage($pageId)) {
            $result['success'] = true;
            $result['msg'] = 'Страница успешно удалена';
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'При удалении страницы произошли ошибки';
        }
        
        return $result;
    }
}