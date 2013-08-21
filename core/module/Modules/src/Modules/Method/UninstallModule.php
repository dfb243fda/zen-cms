<?php

namespace Modules\Method;

use App\Method\AbstractMethod;

class UninstallModule extends AbstractMethod
{
    protected $moduleManager;
    
    protected $translator;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->moduleManager = $this->rootServiceLocator->get('ModuleManager');
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
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
            if ($this->moduleManager->isModuleInstalled($module)) {
                if ($result['success'] = $this->moduleManager->uninstallModule($module)) {
                    $result['msg'] = sprintf($this->translator->translate('Module %s has been uninstalled'), $module);
                } else {
                    $result['errMsg'] = sprintf($this->translator->translate('There are errors while module %s uninstalled'), $module);
                }
            } else {
                $result['errMsg'] = sprintf($this->translator->translate('Module %s does not installed'), $module);
            }
        }
        return $result;
    }
}