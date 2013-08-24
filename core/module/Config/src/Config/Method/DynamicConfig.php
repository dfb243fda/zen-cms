<?php

namespace Config\Method;

use App\Method\AbstractMethod;
use Config\Model\Config;

class DynamicConfig extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $request;
    
    protected $configModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->getServiceLocator();
        
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->request = $this->rootServiceLocator->get('request');
        $this->configModel = new Config($this->rootServiceLocator);
    }
    
    public function main()
    {        
        if (null !== $this->params()->fromRoute('id')) {
            $currentTab = (string)$this->params()->fromRoute('id');
            $this->configModel->setCurrentTab($currentTab);
        }
        $this->configModel->init();
        
        $tabs = $this->configModel->getTabs();
        $form = $this->configModel->getForm();
        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMsg = array();
        
        $result = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->configModel->edit($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Настройки успешно обновлены');
                    $this->redirect()->refresh();
                }

                return array(
                    'success' => true,
                    'msg' => 'Настройки успешно обновлены',
                );         
            } else {
                $result['success'] = false;
                $formMsg = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['tabs'] = $tabs;
        $result['contentTemplate'] = array(
            'name' => 'content_template/Config/dynamic_config.phtml',
            'data' => array(
                'formConfig' => $formConfig,
                'formValues' => $formValues,             
                'formMsg'    => $formMsg,
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