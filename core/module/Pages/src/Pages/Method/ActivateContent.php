<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class ActivateContent extends AbstractMethod
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
            
            if ($this->contentModel->activateContent($contentId)) {
                $result['success'] = true;
                $result['msg'] = 'Содержимое активировано';
            } else {
                $result['success'] = false;
                $result['errMsg'] = 'При активации содержимого произошли проблемы';
            }
        }
        
        return $result;
    }
}