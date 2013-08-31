<?php

namespace ChameleonTheme\Method;

use App\Method\AbstractMethod;

class AdminMainPage extends AbstractMethod
{
    public function main()
    {
        $mainPageService = $this->serviceLocator->get('ChameleonTheme\Service\AdminMainPage');
        
        $result = array();        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ChameleonTheme/main_page_content.phtml',
            'data' => array(
                'modulesList' => $mainPageService->getModulesList(),
            ),
        );        

        return $result;
    }
    
}