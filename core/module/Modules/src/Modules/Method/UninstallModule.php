<?php

namespace Modules\Method;

use App\Method\AbstractMethod;

class UninstallModule extends AbstractMethod
{        
    public function main()
    {
        $moduleManager = $this->serviceLocator->get('moduleManager');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array(
            'success' => false,
        );
        
        if (null === $this->params()->fromPost('module')) {
            $result['errMsg'] = $translator->translate('Wrong parameters transferred');
        } else {
            $module = (string)$this->params()->fromPost('module');
            if ($moduleManager->isModuleInstalled($module)) {
                if ($result['success'] = $moduleManager->uninstallModule($module)) {
                    $result['msg'] = sprintf($translator->translate('Module %s has been uninstalled'), $module);
                } else {
                    $result['errMsg'] = sprintf($translator->translate('There are errors while module %s uninstalled'), $module);
                }
            } else {
                $result['errMsg'] = sprintf($translator->translate('Module %s does not installed'), $module);
            }
        }
        return $result;
    }
}