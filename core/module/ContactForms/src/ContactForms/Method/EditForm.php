<?php

namespace ContactForms\Method;

use App\Method\AbstractMethod;
use ContactForms\Model\Forms as FormsModel;
use Zend\Form\Factory;
use Zend\Validator\AbstractValidator;

class EditForm extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $formsModel;
    
    protected $db;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        
        $this->formsModel = new FormsModel($this->rootServiceLocator);
        
        $this->db = $this->rootServiceLocator->get('db');
        
        AbstractValidator::setDefaultTranslator($this->rootServiceLocator->get('translator'));
    }
    
    public function main()
    {
        if (null === $this->params()->fromRoute('id')) {
            throw new \Exception('wrong parameters transferred');
        }
        $formId = (int)$this->params()->fromRoute('id');
        
        $formConfig = $this->formsModel->getFormConfig();   
   
        $prg = $this->prg();
        
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $formValues = $this->formsModel->getFormValues($formId);
            
            if (null === $formValues) {
                throw new \Exception('form ' . $formId . ' does not find');
            }
            
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/ContactForms/form.phtml',
                    'data' => array(
                        'task'       => 'add',
                        'formConfig' => $formConfig,
                        'formValues' => $formValues,
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
        
        $post = $prg;
        
        $factory = new Factory($this->rootServiceLocator->get('FormElementManager'));
        
        $form = $factory->createForm($formConfig);
        
        $form->setData($post);
        
        $formMsg = array();
        
        $msg = array();
        if ($form->isValid()) {
            $formValues = $form->getData();
            
            $this->formsModel->editContactForm($formId, $formValues);
            
            $msg[] = 'Форма успешно изменена';
        } else {
            $formValues = $form->getData();
            $formMsg = $form->getMessages();
        }
        
        $result = array(
            'msg' => $msg,
            'contentTemplate' => array(
                'name' => 'content_template/ContactForms/form.phtml',
                'data' => array(
                    'task'       => 'add',
                    'formConfig' => $formConfig,
                    'formValues' => $formValues,
                    'formMsg'    => $formMsg,
                ),
            ),            
        );
        
        return $result;
    }
}