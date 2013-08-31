<?php

namespace Config\Method;

use App\Method\AbstractMethod;

class DynamicConfig extends AbstractMethod
{
    public function main()
    {
        $configSettings = $this->serviceLocator->get('Config\Service\ConfigSettings');
        $request = $this->serviceLocator->get('request');
        
        if (null !== $this->params()->fromRoute('id')) {
            $currentTab = (string)$this->params()->fromRoute('id');
            $configSettings->setCurrentTab($currentTab);
        }
        
        $tabs = $configSettings->getTabs();
        $form = $configSettings->getForm();
                
        $result = array();
        
        if ($request->isPost()) {            
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                if ($configSettings->edit($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Настройки успешно обновлены');
                        $this->redirect()->refresh();
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Настройки успешно обновлены',
                    );         
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При обновлении настроек произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        }
        
        $result['tabs'] = $tabs;
        $result['contentTemplate'] = array(
            'name' => 'content_template/Config/dynamic_config.phtml',
            'data' => array(
                'form' => $form,
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