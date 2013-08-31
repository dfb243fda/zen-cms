<?php

namespace ChameleonTheme\Method;

use App\Method\AbstractMethod;

class AdminMainMenu extends AbstractMethod
{        
    public function main()
    {        
        $mainMenuService = $this->serviceLocator->get('ChameleonTheme\Service\AdminMainMenu');
                        
        return array(
            'mainMenu' => $mainMenuService->getMainMenu(),
        );
    }
}