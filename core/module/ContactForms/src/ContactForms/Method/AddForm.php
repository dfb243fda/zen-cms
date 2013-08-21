<?php

namespace ContactForms\Method;

use App\Method\AbstractMethod;
use ContactForms\Model\Forms as FormsModel;
use Zend\Form\Factory;
use Zend\Validator\AbstractValidator;

class AddForm extends AbstractMethod
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
        $formConfig = $this->formsModel->getFormConfig();
                
        $prg = $this->prg();
        
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $formValues = $this->formsModel->getDefaultFormValues();
            
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
        
        if ($form->isValid()) {
            $formValues = $form->getData();
            
            $formId = $this->formsModel->addContactForm($formValues);
            
            $this->flashMessenger()->addSuccessMessage('Форма успешно добавлена');
            
            return $this->redirect()->toRoute('admin/method', array(
                'module' => 'ContactForms',
                'method' => 'EditForm',
                'id'     => $formId,
            ));
        } else {
            $formValues = $form->getData();
            $formMsg = $form->getMessages();
        }
        
        return array(
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
    }
}