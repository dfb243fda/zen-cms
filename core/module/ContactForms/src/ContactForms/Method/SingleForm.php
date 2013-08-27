<?php

namespace ContactForms\Method;

use Pages\AbstractMethod\FeContentMethod;
use ContactForms\Form\Form as ContactForm;
use ContactForms\Model\Forms as ContactFormsModel;

use Zend\Validator\AbstractValidator;

class SingleForm extends FeContentMethod
{
    public function main($formObjectId = null)
    {
        if (null === $this->contentData) {
            if (null === $formObjectId) {
                throw new \Exception('form object id not transferred');
            }
        } else {
            $formObjectId = $this->contentData['fieldGroups']['common']['fields']['form_id'];
        }
        $formObjectId = (int)$formObjectId;
        
        $translator = $this->serviceLocator->get('translator');
        $request = $this->serviceLocator->get('request');
        $formsCollection = $this->serviceLocator->get('ContactForms\Collection\ContactForms');
        
        if (!($formEntity = $formsCollection->getFormEntityByObjectId($formObjectId))) {
            throw new \Exception('form ' . $formObjectId . ' does not found');
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

class SingleForm2 extends FeContentMethod
{
    protected $rootServiceLocator;
    
    protected $db;
    
    protected $table = 'contact_forms';
    
    public function init()
    {
        $this->rootServiceLocator = $this->getServiceLocator();
        $this->contactFormsModel = new ContactFormsModel($this->rootServiceLocator);
        $this->db = $this->rootServiceLocator->get('db');
        $this->table = DB_PREF . $this->table;
        $this->translator = $this->rootServiceLocator->get('translator');
        
        $this->request = $this->rootServiceLocator->get('request');
        
        AbstractValidator::setDefaultTranslator($this->translator);
    }
    
    public function main($formObjectId = null)
    {
        if (null === $this->contentData) {
            if ($formObjectId === null) {
                throw new \Exception('form object id not transferred');
            }
        } else {
            $formObjectId = $this->contentData['fieldGroups']['common']['fields']['form_id'];
        }
        
        $formObjectId = (int)$formObjectId;
        
        $sqlRes = $this->db->query('select id, template from ' . $this->table . ' where object_id = ? limit 1', array($formObjectId))->toArray();
        
        $formMsg = array();
        $msg = '';
        
        $result = array();
        
        if (empty($sqlRes)) {
            $formHtml = 'Форма не найдена';
        } else {            
            $template = $sqlRes[0]['template'];
            
            $contactForm = new ContactForm(array(
                'serviceManager' => $this->rootServiceLocator,
                'template' => $template,
            ));
     
            if ($this->request->isPost() && $this->request->getPost('contact-form-id') == $sqlRes[0]['id']) {
                $post = array_merge($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
                
                $zendForm = $contactForm->getZendForm();
                $zendForm->setData($post);
                if ($zendForm->isValid()) {
                    $data = $zendForm->getData();  
                    
                    if ($this->request->isXmlHttpRequest()) {
                        if ($this->contactFormsModel->sendMessages($formObjectId, $data, $zendForm)) {  
                            $result['success'] = true;
                            $result['msg'] = $this->translator->translate('ContactForms:Message sent successfully');
                        } else {
                            $result['success'] = false;
                            $result['errMsg'] = $this->translator->translate('ContactForms:Message sent successfully');
                        }                
                    } else {
                        if ($this->contactFormsModel->sendMessages($formObjectId, $data, $zendForm)) {                        
                            $this->flashMessenger()->addSuccessMessage($this->translator->translate('ContactForms:Message sent successfully'));
                        } else {
                            $this->flashMessenger()->addErrorMessage($this->translator->translate('ContactForms:Message sent failure'));
                        }

                        return $this->redirect()->refresh();
                    }
                    
                } else {
                    if ($this->request->isXmlHttpRequest()) {
                        $result['success'] = false;
                        $result['formMsg'] = $zendForm->getMessages();
                    }
                }
            }
            
            $this->rootServiceLocator->get('viewHelperManager')->get('headScript')
                ->appendFile(ROOT_URL_SEGMENT . '/js/core/jquery_plugins/jquery.form.js')
                ->appendFile(ROOT_URL_SEGMENT . '/js/ContactForms/fe_contact_forms.js')
                ->appendFile(ROOT_URL_SEGMENT . '/js/core/jquery_plugins/jquery-toastmessage-plugin/javascript/jquery.toastmessage.js');
            
            $contactFormsDirectUrl = $this->url()->fromRoute('direct', array(
                'module' => 'ContactForms',
                'method' => 'SingleForm',
                'param1' => $sqlRes[0]['id'],
            ));
            
            $this->rootServiceLocator->get('viewHelperManager')->get('inlineScript')
                ->appendScript('zen.fe_contact_forms.init("' . 'contact-form-' . $sqlRes[0]['id'] . '", "' . $contactFormsDirectUrl . '");');
            
            $this->rootServiceLocator->get('viewHelperManager')->get('headLink')
                 ->appendStylesheet(ROOT_URL_SEGMENT . '/js/core/jquery_plugins/jquery-toastmessage-plugin/resources/css/jquery.toastmessage.css');
            
            $formHtml = '';
            $formHtml .= '<form method="post" id="contact-form-' . $sqlRes[0]['id'] . '" enctype="multipart/form-data">';
            $formHtml .= '<input type="hidden" name="contact-form-id" value="' . $sqlRes[0]['id'] . '" />';
            $formHtml .= $contactForm->getHtml();
            $formHtml .= '</form>';
        }
        
        $result['form_html'] = $formHtml;
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = implode(', ', $this->flashMessenger()->getSuccessMessages());
        }
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = implode(', ', $this->flashMessenger()->getErrorMessages());
        }
        
        return $result;
    }
}