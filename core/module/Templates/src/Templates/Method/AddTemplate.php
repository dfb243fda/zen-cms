<?php

namespace Templates\Method;

use App\Method\AbstractMethod;
use Templates\Model\Templates;

class AddTemplate extends AbstractMethod
{
    protected $templatesModel;    
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->templatesModel = new Templates($this->rootServiceLocator, $this->url());
        $this->moduleManager = $this->rootServiceLocator->get('moduleManager');
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {           
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
        
        $formConfig = $this->templatesModel->getFormConfig();
        
        $formMsg = array();
        
        if ($this->request->isPost()) {
            $formValues = $this->request->getPost();
            
            $factory = new \Zend\Form\Factory($this->rootServiceLocator->get('FormElementManager'));

            $form = $factory->createForm($formConfig);         
            $form->setData($formValues);
            
            if ($form->isValid()) {
                $formValues = $form->getData();
                
                $this->templatesModel->addTemplate($module, $method, $templateType, $formValues);                
                
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
                    'content' => $this->templatesModel->getPageTemplateDefaultContent(),
                    'markers' => $this->templatesModel->getPageTemplateDefaultMarkers(),
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