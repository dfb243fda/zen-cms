<?php

namespace Templates\Method;

use App\Method\AbstractMethod;

class AddTemplate extends AbstractMethod
{    
    public function main()
    {           
        $request = $this->serviceLocator->get('request');
        
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
        
        $templatesFormFactory = $this->serviceLocator->get('Templates\FormFactory\TemplatesFormFactory');
        
        $templatesFormFactory->setTemplateType($templateType);

        $form = $templatesFormFactory->getForm();        
        
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {                
                $templatesCollection = $this->serviceLocator->get('Templates\Collection\TemplatesCollection');
                
                if ($templateId = $templatesCollection->addTemplate($module, $method, $templateType, $form->getData())) {
                    $urlParams = array(
                        'templateModule' => $module,                    
                    );
                    if ($method) {
                        $urlParams['templateMethod'] = $method;
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