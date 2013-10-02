<?php

namespace AdminPanel\Method;

use App\Method\AbstractMethod;

class AdminMainMenu extends AbstractMethod
{        
    public function main()
    {        
        $mainMenuService = $this->serviceLocator->get('AdminPanel\Service\AdminMainMenu');
                        
        return array(
            'mainMenu' => $mainMenuService->getMainMenu(),
        );
    }
}