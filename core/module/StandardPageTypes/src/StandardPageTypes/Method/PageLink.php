<?php

namespace StandardPageTypes\Method;

use Pages\Entity\FePageMethod;
use App\Utility\GeneralUtility;

class PageLink extends FePageMethod
{        
    public function init()
    {
        
    }
    
    public function main()
    {
        $url = $this->pageData['fieldGroups']['page-link']['fields']['url'];
        
        if (!GeneralUtility::isValidUrl($url)) {
            $url = ROOT_URL_SEGMENT . $url;
        }
        
        return $this->redirect()->toUrl($url);
    }
}