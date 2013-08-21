<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;
use ObjectTypes\Model\Guides;

class AddGuideItem extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $guidesModel;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->guidesModel = new Guides($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }
    
    public function main()
    {
        $result = array();
        
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        } 
        
        $guideId = (int)$this->params()->fromRoute('id');
        
        $this->guidesModel->setGuideId($guideId);
        
        $form = $this->guidesModel->getGuideItemForm();        
        $formConfig = $form['formConfig'];
        $formValues = $form['formValues'];
        $formMessages = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->guidesModel->addGuideItem($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
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