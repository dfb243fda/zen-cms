<?php

namespace ChameleonTheme\Method;

use App\Method\AbstractMethod;

class AdminMainPage extends AbstractMethod
{
    public function main()
    {
        $result = array();
        
        $translator = $this->serviceLocator->get('translator');
                
        $moduleManager = $this->serviceLocator->get('moduleManager');
        $modulesList = $moduleManager->getActiveModules();

        foreach ($modulesList as $k => $v) {
            $modulesList[$k]['title'] = $translator->translateI18n($v['title']);
            $modulesList[$k]['description'] = $translator->translateI18n($v['description']);    
            if (!empty($modulesList[$k]['methods'])) {
                foreach ($modulesList[$k]['methods'] as $k2 => $v2) {
                    $modulesList[$k]['methods'][$k2]['title'] = $translator->translateI18n($v2['title']);
                    $modulesList[$k]['methods'][$k2]['description'] = $translator->translateI18n($v2['description']);
                }
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ChameleonTheme/main_page_content.phtml',
            'data' => array(
                'modulesList' => $modulesList,
            ),
        );        

        return $result;
    }
    
}