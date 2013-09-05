<?php

namespace Templates\Method;

use App\Method\AbstractMethod;
use Templates\Model\Templates;

class EditTemplate extends AbstractMethod
{    
    public function main()
    {      
        $request = $this->serviceLocator->get('request');
        
        if (!$this->params()->fromRoute('id')) {
            throw new \Exception('Wrong parameters transferred');
        }
        $id = (int)$this->params()->fromRoute('id');
                
        $templatesFormFactory = $this->serviceLocator->get('Templates\FormFactory\TemplatesFormFactory');
        $templatesFormFactory->setTemplateId($id);
        
        $form = $templatesFormFactory->getForm();
    
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $templateEntity = $this->serviceLocator->get('Templates\Entity\TemplateEntity');
                $templateEntity->setId($id);
                
                $templateEntity->edit($form->getData());
            }
        }        
        
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/Templates/template_form.phtml',
                'data' => array(
                    'form' => $form,
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
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $formConfig = $this->templatesModel->getFormConfig();
        
        $formMsg = array();
        
        if ($this->request->isPost()) {
            $formValues = $this->request->getPost();
            
            $factory = new \Zend\Form\Factory($this->rootServiceLocator->get('FormElementManager'));

            $form = $factory->createForm($formConfig);         
            $form->setData($formValues);
                                    
            if ($form->isValid()) {
                $formValues = $form->getData();
                
                $this->templatesModel->editTemplate($id, $formValues);                
                
                $template = $this->templatesModel->getTemplate($id);
                $urlParams = array(
                    'templateModule' => $template['module'],                    
                );
                if ($template['method']) {
                    $urlParams['templateMethod'] = $template['method'];
                }
                
                $this->flashMessenger()->addSuccessMessage('Шаблон успешно обновлен');
                
                $this->redirect()->toRoute('admin/TemplatesList', $urlParams);
                
                return array(
                    'success' => 1,
                );
            } else {
                $formMsg = $form->getMessages();
            }           
        } else {
            $formValues = $this->templatesModel->getTemplate($id);
            if (null === $formValues) {
                throw new \Exception('Template ' . $id . ' does not exists');
            }
            
            $tmp = $formValues['markers'];
            $formValues['markers'] = '';
            foreach ($tmp as $k=>$v) {
                $formValues['markers'] .= $k . '=' . $v . LF;
            }
        }
             
        
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/Templates/template_form.phtml',
                'data' => array(
                    'formConfig' => $formConfig,
                    'formValues' => $formValues,
                    'formMsg'    => $formMsg,
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