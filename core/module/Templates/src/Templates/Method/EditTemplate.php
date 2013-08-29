<?php

namespace Templates\Method;

use App\Method\AbstractMethod;
use Templates\Model\Templates;

class EditTemplate extends AbstractMethod
{
    protected $templatesModel;    
    
    public function init()
    {
        $this->rootServiceLocator = $this->getServiceLocator();
        $this->templatesModel = new Templates($this->rootServiceLocator, $this->url());
        $this->moduleManager = $this->rootServiceLocator->get('moduleManager');
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {      
        if (!$this->params()->fromRoute('id')) {
            throw new \Exception('Wrong parameters transferred');
        }
        $id = (int)$this->params()->fromRoute('id');
        
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