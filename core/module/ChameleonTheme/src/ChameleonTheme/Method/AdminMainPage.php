<?php

namespace ChameleonTheme\Method;

use App\Method\AbstractMethod;

class AdminMainPage extends AbstractMethod
{
    protected $extKey = 'ChameleonTheme';
    
    protected $rootServiceLocator;
    
    protected $translator;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
    }

    public function main()
    {
        $result = array();
                
        $moduleManager = $this->rootServiceLocator->get('moduleManager');
        $modulesList = $moduleManager->getActiveModules();

        foreach ($modulesList as $k => $v) {
            $modulesList[$k]['title'] = $this->translator->translateI18n($v['title']);
            $modulesList[$k]['description'] = $this->translator->translateI18n($v['description']);    
            if (!empty($modulesList[$k]['methods'])) {
                foreach ($modulesList[$k]['methods'] as $k2 => $v2) {
                    $modulesList[$k]['methods'][$k2]['title'] = $this->translator->translateI18n($v2['title']);
                    $modulesList[$k]['methods'][$k2]['description'] = $this->translator->translateI18n($v2['description']);
                }
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/' . $this->extKey . '/main_page_content.phtml',
            'data' => array(
                'modulesList' => $modulesList,
            ),
        );        

        return $result;
    }
}