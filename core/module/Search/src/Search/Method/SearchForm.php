<?php

namespace Search\Method;

use Pages\AbstractMethod\FeContentMethod;

class SearchForm extends FeContentMethod
{    
    public function main()
    {
        $pageService = $this->serviceLocator->get('Pages\Service\Page');
        
        $resultPageId = $this->contentData['fieldGroups']['common']['fields']['result_page_id'];
        
        $resultPageLink = $pageService->getPageUrl($resultPageId);
        
        return array(
            'resultPageLink' => $resultPageLink,
        );
    }
    
}