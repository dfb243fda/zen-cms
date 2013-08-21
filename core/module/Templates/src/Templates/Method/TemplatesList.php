<?php

namespace Templates\Method;

use App\Method\AbstractMethod;
use Templates\Model\Templates;

class TemplatesList extends AbstractMethod
{
    protected $templatesModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->templatesModel = new Templates($this->rootServiceLocator, $this->url());
        $this->moduleManager = $this->rootServiceLocator->get('moduleManager');
    }
    
    public function main()
    {           
        if (!$this->params()->fromRoute('templateModule')) {
            $templates = array();
            $createTemplateLink = null;
        } elseif (!$this->params()->fromRoute('templateMethod')) {
            $module = $this->params()->fromRoute('templateModule');
            $templates = $this->templatesModel->getMethodTemplates($module, '');
            $createTemplateLink = $this->url()->fromRoute('admin/AddTemplate', array(
                'templateModule' => $module,
            ));
        } else {
            $module = $this->params()->fromRoute('templateModule');
            $method = $this->params()->fromRoute('templateMethod');
         
            $templates = $this->templatesModel->getMethodTemplates($module, $method);
            $createTemplateLink = $this->url()->fromRoute('admin/AddTemplate', array(
                'templateModule' => $module,
                'templateMethod' => $method,
            ));
        }
        
        $modules = $this->templatesModel->getModules();
        
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/Templates/templates_list.phtml',
                'data' => array(
                    'modules' => $modules,
                    'createTemplateLink' => $createTemplateLink,
                    'templates' => $templates,
                ),
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