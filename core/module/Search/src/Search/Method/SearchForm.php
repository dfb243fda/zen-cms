<?php

namespace Search\Method;

use Pages\AbstractMethod\FeContentMethod;

class SearchForm extends FeContentMethod
{    
    public function main()
    {
        $pageUrlService = $this->serviceLocator->get('Pages\Service\PageUrl');
        
        $resultPageId = $this->contentData['fieldGroups']['common']['fields']['result_page_id'];
        
        $resultPageLink = $pageUrlService->getPageUrl($resultPageId);
        
        return array(
            'resultPageLink' => $resultPageLink,
        );
    }
    
}