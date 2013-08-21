<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class DeleteContent extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $contentModel;
    
    protected $request;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->contentModel = new \Pages\Model\PagesContent($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {
        $result = array();
        
        if (null === $this->request->getPost('id')) {
            $result['success'] = false;
            $result['errMsg'] = 'Не передан параметр id';
        } else {
            $contentId = (int)$this->request->getPost('id');
            
            if ($this->contentModel->deleteContent($contentId)) {
                $result['success'] = true;
                $result['msg'] = 'Содержимое удалено';
            } else {
                $result['success'] = false;
                $result['errMsg'] = 'При удалении содержимого произошли проблемы';
            }
        }
        
        return $result;
    }
}