<?php

namespace Templates\Method;

use App\Method\AbstractMethod;

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
                $templateEntity->setTemplateId($id);
                
                if ($templateEntity->editTemplate($form->getData())) {
                    $templateData = $templateEntity->getData();
                    $urlParams = array(
                        'templateModule' => $templateData['module'],                    
                    );
                    if ($templateData['method']) {
                        $urlParams['templateMethod'] = $templateData['method'];
                    }

                    $this->flashMessenger()->addSuccessMessage('Шаблон успешно обновлен');

                    return $this->redirect()->toRoute('admin/TemplatesList', $urlParams);
                }
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
    }
}