<?php

namespace AdminPanel\Method;

use App\Method\AbstractMethod;

class AdminMainPage extends AbstractMethod
{
    public function main()
    {
        $mainPageService = $this->serviceLocator->get('AdminPanel\Service\AdminMainPage');
        
        $result = array();        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/AdminPanel/main_page_content.phtml',
            'data' => array(
                'modulesList' => $mainPageService->getModulesList(),
            ),
        );        

        return $result;
    }
    
}