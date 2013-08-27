<?php

namespace ContactForms\Method;

use App\Method\AbstractMethod;
use Zend\Http\PhpEnvironment\Response;

class AddForm extends AbstractMethod
{
    public function main()
    {     
        $contactFormsCollection = $this->serviceLocator->get('ContactForms\Collection\ContactForms');
                
        $form = $contactFormsCollection->getAdminForm();
   
        $prg = $this->prg();
        
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {            
            $result = array(
                'contentTemplate' => array(
                    'name' => 'content_template/ContactForms/form.phtml',
                    'data' => array(
                        'task' => 'add',
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
        
        if ($form->isValid()) {            
            if ($formId = $contactFormsCollection->addContactForm($form->getData())) {
                $this->flashMessenger()->addSuccessMessage('Форма успешно добавлена');
                
                return $this->redirect()->toRoute('admin/method', array(
                    'module' => 'ContactForms',
                    'method' => 'EditForm',
                    'id'     => $formId,
                ));
            }
        }
        
        $result = array(
            'contentTemplate' => array(
                'name' => 'content_template/ContactForms/form.phtml',
                'data' => array(
                    'task' => 'add',
                    'form' => $form,
                ),
            ),            
        );
        
        return $result;
    }    
}