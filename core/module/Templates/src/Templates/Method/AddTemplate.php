<?php

namespace Templates\Method;

use App\Method\AbstractMethod;
use Templates\Model\Templates;

class AddTemplate extends AbstractMethod
{    
    public function main()
    {           
        $templatesModel = new Templates($this->serviceLocator, $this->url());
        $moduleManager = $this->serviceLocator->get('moduleManager');
        $request = $this->serviceLocator->get('request');
        
        if (!$this->params()->fromRoute('templateModule')) {
            throw new \Exception('Wrong parameters transferred');
        } elseif (!$this->params()->fromRoute('templateMethod')) {
            $module = (string)$this->params()->fromRoute('templateModule');
            $method = '';
            $templateType = 'page_template';
        } else {
            $module = (string)$this->params()->fromRoute('templateModule');
            $method = (string)$this->params()->fromRoute('templateMethod');
            $templateType = 'content_template';
        }
        
        $formConfig = $templatesModel->getFormConfig();
        
        $formMsg = array();
        
        if ($request->isPost()) {            
            $factory = new \Zend\Form\Factory($this->serviceLocator->get('FormElementManager'));

            $form = $factory->createForm($formConfig);         
            $form->setData($this->params()->fromPost());
            
            if ($form->isValid()) {
                $formValues = $form->getData();
                
                $templatesModel->addTemplate($module, $method, $templateType, $formValues);                
                
                $urlParams = array(
                    'templateModule' => $module,                    
                );
                if ($method) {
                    $urlParams['templateMethod'] = $method;
                }
                
                $this->flashMessenger()->addSuccessMessage('Шаблон успешно создан');
                
                $this->redirect()->toRoute('admin/TemplatesList', $urlParams);
                
                return array(
                    'success' => 1,
                );
            } else {
                $formMsg = $form->getMessages();
                $formValues = $form->getData();
            }           
        } else {
            if ('' == $method) {
                $formValues = array(
                    'content' => $templatesModel->getPageTemplateDefaultContent(),
                    'markers' => $templatesModel->getPageTemplateDefaultMarkers(),
                );
            } else {
                $formValues = array();
            }   
        }
             
        
        return array(
            'contentTemplate' => array(
                'name' => 'content_template/Templates/template_form.phtml',
                'data' => array(
                    'formConfig' => $formConfig,
                    'formValues' => $formValues,
                    'formMsg'    => $formMsg,
                ),
            ),
        );
    }
}