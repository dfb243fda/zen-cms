<?php

namespace ContactForms\Method;

use App\Method\AbstractMethod;
use Zend\Http\PhpEnvironment\Response;

class EditForm extends AbstractMethod
{
    public function main()
    {
        if (null === $this->params()->fromRoute('id')) {
            throw new \Exception('wrong parameters transferred');
        }
        $formId = (int)$this->params()->fromRoute('id');
     
        $contactFormEntity = $this->serviceLocator->get('ContactForms\Entity\ContactForm');
        
        $contactFormEntity->setFormId($formId)->init();
        
        $form = $contactFormEntity->getAdminForm();
   
        $prg = $this->prg();
        
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {            
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/ContactForms/form.phtml',
                    'data' => array(
                        'task' => 'edit',
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
        }
        
        $post = $prg;
                
        $form->setData($post);
        
        $formMsg = array();
        
        $msg = array();
        if ($form->isValid()) {            
            $contactFormEntity->editContactForm($form->getData());
            
            $msg[] = 'Форма успешно изменена';
        }
        
        $result = array(
            'msg' => $msg,
            'contentTemplate' => array(
                'name' => 'content_template/ContactForms/form.phtml',
                'data' => array(
                    'task' => 'edit',
                    'form' => $form,
                ),
            ),            
        );
        
        return $result;
    }    
}