<?php

namespace Modules\Method;

use App\Method\AbstractMethod;

class ModulesList extends AbstractMethod
{
    public function main()
    {
        $modulesListService = $this->serviceLocator->get('Modules\Service\ModulesList');
        
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/Modules/modules_list.phtml',
                'data' => array(
                    'modules' => $modulesListService->getModulesList(),
                ),
            ),
        );
        
        return $result;
    }    
}