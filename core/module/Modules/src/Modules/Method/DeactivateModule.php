<?php

namespace Modules\Method;

use App\Method\AbstractMethod;

class DeactivateModule extends AbstractMethod
{
    protected $moduleManager;
    
    protected $translator;
    
    public function init()
    {
        $rootServiceManager = $this->serviceLocator->getServiceLocator();
        $this->moduleManager = $rootServiceManager->get('ModuleManager');
        $this->translator = $rootServiceManager->get('translator');
        $this->request = $rootServiceManager->get('request');
    }
        
    public function main()
    {
        $result = array(
            'success' => false,
        );
        
        if (null === $this->request->getPost('module')) {
            $result['errMsg'] = $this->translator->translate('Wrong parameters transferred');
        } else {
            $module = (string)$this->request->getPost('module');
            if ($this->moduleManager->isModuleActive($module)) {
                if ($result['success'] = $this->moduleManager->deactivateModule($module)) {
                    $result['msg'] = sprintf($this->translator->translate('Module %s has been deactivated'), $module);
                } else {
                    $result['errMsg'] = sprintf($this->translator->translate('There are errors while module %s deactivated'), $module);
                }
            } else {
                $result['errMsg'] = sprintf($this->translator->translate('Module %s does not active'), $module);
            }
        }
        return $result;
    }
}