<?php

namespace ThemeSwitch\Method;

use App\Method\AbstractMethod;

class ThemeSwitch extends AbstractMethod
{
    public function main()
    {
        $themeSwitchService = $this->serviceLocator->get('ThemeSwitch\Service\ThemeSwitch');
                
        $themeChanged = false;
        if (null !== $this->params()->fromPost('be_theme')) {
            $theme = (string)$this->params()->fromPost('be_theme');
            $themeSwitchService->setBeTheme($theme);
            $themeChanged = true;
        }
        if (null !== $this->params()->fromPost('fe_theme')) {
            $theme = (string)$this->params()->fromPost('fe_theme');
            $themeSwitchService->setFeTheme($theme);
            $themeChanged = true;
        }
        if ($themeChanged) {
            $this->flashMessenger()->addSuccessMessage('Темы успешно обновлены');
            return $this->redirect()->refresh();
        }
        
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/ThemeSwitch/themes.phtml',
                'data' => array(
                    'themes' => $themeSwitchService->getThemes(),
                ),
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
                
        return $result;
    }
}