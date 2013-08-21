<?php

namespace Search\Method;

use Pages\Entity\FeContentMethod;

class SearchForm extends FeContentMethod
{
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->db = $this->rootServiceLocator->get('db');
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->pageService = $this->rootServiceLocator->get('Pages\Service\Page');
        
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {
        $resultPageId = $this->contentData['fieldGroups']['common']['fields']['result_page_id'];
        
        $resultPageLink = $this->pageService->getPageUrl($resultPageId);
        
        return array(
            'resultPageLink' => $resultPageLink,
        );
    }
    
}