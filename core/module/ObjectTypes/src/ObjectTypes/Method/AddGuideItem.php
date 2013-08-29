<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;
use ObjectTypes\Model\Guides;

class AddGuideItem extends AbstractMethod
{
    public function main()
    {
        $guidesModel = new Guides($this->serviceLocator);
        $request = $this->serviceLocator->get('request');
        
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $guideId = (int)$this->params()->fromRoute('id');
        
        $guidesModel->setGuideId($guideId);
        
        $form = $guidesModel->getGuideItemForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($request->isPost()) {
            $tmp = $guidesModel->addGuideItem($this->params()->fromPost());
            if ($tmp['success']) {
                if (!$request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Термин успешно добавлен');
                    
                    $this->redirect()->toRoute('admin/method', array(
                        'module' => 'ObjectTypes',
                        'method' => 'EditGuideItem',
                        'id'     => $tmp['guideItemId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Термин успешно добавлен',
                );         
            } else {
                $result['success'] = false;
                $formMessages = $tmp['form']->getMessages();
                $formValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/ObjectTypes/guide_item_form_view.phtml',
            'data' => array(
                'formConfig' => $formConfig,
                'formValues' => $formValues,
                'formMsg'    => $formMessages,
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