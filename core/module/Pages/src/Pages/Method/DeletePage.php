<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class DeletePage extends AbstractMethod
{    
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $pagesModel;
    
    protected $request;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->pagesModel = new \Pages\Model\Pages($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }


    public function main()
    {
        $result = array(
            'success' => false,
        );
        
        if (null === $this->request->getPost('id')) {
            $result['errMsg'] = 'Не передан параметр id';
            return $result;
        }
        $pageId = (int)$this->request->getPost('id');
        
        if ($this->pagesModel->deletePage($pageId)) {
            $result['success'] = true;
            $result['msg'] = 'Страница успешно удалена';
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'При удалении страницы произошли ошибки';
        }
        
        return $result;
    }
}