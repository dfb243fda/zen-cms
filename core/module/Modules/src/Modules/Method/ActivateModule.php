<?php

namespace Modules\Method;

use App\Method\AbstractMethod;

class ActivateModule extends AbstractMethod
{        
    public function main()
    {
        $translator = $this->serviceLocator->get('translator');
        $moduleManager = $this->serviceLocator->get('moduleManager');
        
        $result = array(
            'success' => false,
        );
        
        if (null === $this->params()->fromPost('module')) {
            $result['errMsg'] = $translator->translate('Wrong parameters transferred');
        } else {
            $module = (string)$this->params()->fromPost('module');
            if ($moduleManager->isModuleInstalled($module) && !$moduleManager->isModuleActive($module)) {
                if ($result['success'] = $moduleManager->activateModule($module)) {
                    $result['msg'] = sprintf($translator->translate('Module %s has been activated'), $module);
                } else {
                    $result['errMsg'] = sprintf($translator->translate('There are errors while module %s activated'), $module);
                }
            } else {
                $result['errMsg'] = sprintf($translator->translate('Module %s does not deactive'), $module);
            }
        }
        return $result;
    }
}