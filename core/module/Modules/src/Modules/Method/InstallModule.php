<?php

namespace Modules\Method;

use App\Method\AbstractMethod;

class InstallModule extends AbstractMethod
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
            if ($moduleManager->isModuleExists($module)) {
                if ($moduleManager->isModuleInstalled($module)) {
                    $result['errMsg'] = sprintf($translator->translate('Module %s is already installed'), $module);
                } else {
                    if ($result['success'] = $moduleManager->installModule($module)) {
                        $result['msg'] = sprintf($translator->translate('Module %s has been installed'), $module);
                    } else {
                        $result['errMsg'] = sprintf($translator->translate('There are errors while module %s installed'), $module);
                    }
                }
            } else {
                $result['errMsg'] = sprintf($translator->translate('Module %s does not find'), $module);
            }
        }
        return $result;
    }
}