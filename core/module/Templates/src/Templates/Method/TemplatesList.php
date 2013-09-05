<?php

namespace Templates\Method;

use App\Method\AbstractMethod;

class TemplatesList extends AbstractMethod
{
    public function main()
    {           
        $templatesList = $this->serviceLocator->get('Templates\Service\TemplatesList');
        
        if (!$this->params()->fromRoute('templateModule')) {
            $templates = array();
            $createTemplateLink = null;
        } elseif (!$this->params()->fromRoute('templateMethod')) {
            $module = $this->params()->fromRoute('templateModule');
            $templates = $templatesList->getMethodTemplates($module, '');
            $createTemplateLink = $this->url()->fromRoute('admin/AddTemplate', array(
                'templateModule' => $module,
            ));
        } else {
            $module = $this->params()->fromRoute('templateModule');
            $method = $this->params()->fromRoute('templateMethod');
         
            $templates = $templatesList->getMethodTemplates($module, $method);
            $createTemplateLink = $this->url()->fromRoute('admin/AddTemplate', array(
                'templateModule' => $module,
                'templateMethod' => $method,
            ));
        }
        
        $modules = $templatesList->getModules();
        
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