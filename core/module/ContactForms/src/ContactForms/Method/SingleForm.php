<?php

namespace ContactForms\Method;

use Pages\AbstractMethod\FeContentMethod;
use ContactForms\Form\Form as ContactForm;
use ContactForms\Model\Forms as ContactFormsModel;

use Zend\Validator\AbstractValidator;

class SingleForm extends FeContentMethod
{
    public function main($formId = null)
    {
        $translator = $this->serviceLocator->get('translator');
        $request = $this->serviceLocator->get('request');
        $formsCollection = $this->serviceLocator->get('ContactForms\Collection\ContactForms');
        
        if (null === $this->contentData) {
            if (null === $formId) {
                throw new \Exception('form id does not transferred');
            }
            if (!($formEntity = $formsCollection->getFormEntity($formId))) {
                throw new \Exception('form ' . $formId . ' does not found');
            }
        } else {
            $formObjectId = $this->contentData['fieldGroups']['common']['fields']['form_id'];
            
            $formObjectId = (int)$formObjectId;
            
            if (!($formEntity = $formsCollection->getFormEntityByObjectId($formObjectId))) {
                throw new \Exception('form object ' . $formObjectId . ' does not found');
            }
        }        
        
        $formId = $formEntity->getFormId();
        
        $result = array();        
        
        if ($this->params()->fromPost('contact-form-id') == $formId) {
            $post = array_merge($request->getPost()->toArray(), $request->getFiles()->toArray());

            $zendForm = $formEntity->getContactForm();
            $zendForm->setData($post);
            if ($zendForm->isValid()) {
                $data = $zendForm->getData();  

                if ($request->isXmlHttpRequest()) {
                    if ($formEntity->sendMessages($data)) {  
                        $result['success'] = true;
                        $result['msg'] = $translator->translate('ContactForms:Message sent successfully');
                    } else {
                        $result['success'] = false;
                        $result['errMsg'] = $translator->translate('ContactForms:Message sent successfully');
                    }                
                } else {
                    if ($formEntity->sendMessages($data)) {                        
                        $this->flashMessenger()->addSuccessMessage($translator->translate('ContactForms:Message sent successfully'));
                    } else {
                        $this->flashMessenger()->addErrorMessage($translator->translate('ContactForms:Message sent failure'));
                    }

                    return $this->redirect()->refresh();
                }

            } else {
                if ($request->isXmlHttpRequest()) {
                    $result['success'] = false;
                    $result['formMsg'] = $zendForm->getMessages();
                }
            }
        }
        
        
        $result['formId'] = $formId;
        
        $result['formHtml'] = $formEntity->getContactFormHtml();
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = implode(', ', $this->flashMessenger()->getSuccessMessages());
        }
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = implode(', ', $this->flashMessenger()->getErrorMessages());
        }
        
        return $result;
    }
}