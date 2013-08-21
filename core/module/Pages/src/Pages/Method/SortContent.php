<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class SortContent extends AbstractMethod
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
        
        if (null !== $this->request->getPost('beforeContentId') &&
            null !== $this->request->getPost('contentId') &&
            null !== $this->request->getPost('markerId')) {
            
            $beforeContentId = (int)$this->request->getPost('beforeContentId');
            $contentId = (int)$this->request->getPost('contentId');
            $markerId = (int)$this->request->getPost('markerId');
            
            $this->contentModel->sortContent($beforeContentId, $contentId, $markerId);
            $result['success'] = true;
        } else {
            $result['success'] = false;
        }
        
        return $result;
    }
}