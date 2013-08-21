<?php

namespace Modules\Method;

use App\Method\AbstractMethod;
use Modules\Model\Modules as ModulesModel;

use Zend\Stdlib\ResponseInterface as Response;

class ModuleInfo extends AbstractMethod
{
    protected $moduleManager;
    
    protected $translator;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->moduleManager = $this->rootServiceLocator->get('ModuleManager');
        $this->translator = $this->rootServiceLocator->get('translator');
    
        $this->modulesModel = new ModulesModel($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }
        
    public function main()
    {
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        
        $moduleKey = (string)$this->params()->fromRoute('id');
        
        if (!$this->moduleManager->isModuleActive($moduleKey)) {
            $result['errMsg'] = 'Модуль ' . $moduleKey . ' не установлен, либо отключен';
            return $result;
        }
        
        $moduleConfig = $this->moduleManager->getModuleConfig($moduleKey);
        
        $moduleConfig['title'] = $this->translator->translateI18n($moduleConfig['title']);
        $moduleConfig['description'] = $this->translator->translateI18n($moduleConfig['description']);
        
        if (!empty($moduleConfig['methods'])) {
            foreach ($moduleConfig['methods'] as $k=>$v) {
                $moduleConfig['methods'][$k]['title'] = $this->translator->translateI18n($v['title']);
                $moduleConfig['methods'][$k]['description'] = $this->translator->translateI18n($v['description']);
            }
        }
        
        $dbDifference = $this->modulesModel->getDbDifference($moduleKey);
        $filesDifference = $this->modulesModel->getFilesDifference($moduleKey);
        
        $prg = $this->prg();
            
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg !== false) {
            $post = $prg;
            
            if (isset($prg['task'])) {
                if ('db_difference' == $prg['task']) {
                    $this->modulesModel->updateDbDifference($dbDifference, $post);
                    return $this->redirect()->refresh();
                } elseif ('files_difference' == $prg['task']) {
                    $this->modulesModel->updateFilesDifference($filesDifference, $post);
                    return $this->redirect()->refresh();
                } elseif ('full_update' == $prg['task']) {
                    if ($this->moduleManager->updateModule($moduleKey)) {
                        $this->flashMessenger()->addSuccessMessage('Модуль успешно обновлен');
                    } else {
                        $this->flashMessenger()->addErrorMessage('При обновлении модуля произошли ошибки');
                    }
                    return $this->redirect()->refresh();
                }
            }
            
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Modules/module_info.phtml',
            'data' => array(
                'moduleConfig'    => $moduleConfig,
                'filesDifference' => $filesDifference,
                'dbDifference'    => $dbDifference,
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