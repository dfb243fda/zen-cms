<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class EditPage extends AbstractMethod
{    
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $pagesModel;
    
    protected $request;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->pagesModel = new \Pages\Model\Pages($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }


    public function main()
    {
        $result = array();   
        
        if ($this->params()->fromRoute('id')) {
            $pageId = (int)$this->params()->fromRoute('id');
        } else {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $this->pagesModel->setObjectTypeId($objectTypeId);
        }
        if (null !== $this->params()->fromRoute('pageTypeId')) {  
            $pageTypeId = (int)$this->params()->fromRoute('pageTypeId');
            $this->pagesModel->setPageTypeid($pageTypeId);
        }
        
        
        $form = $this->pagesModel->getPageForm($pageId);
        
        $pageFormConfig = $form['formConfig'];
        $pageValues = $form['formValues'];
        
        $pageFormMsg = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->pagesModel->editPage($pageId, $this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Страница успешно обновлена');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Pages',
                        'method' => 'EditPage',
                        'id' => $pageId,
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Страница успешно обновлена',
                );         
            } else {
                $result['success'] = false;
                $pageFormMsg = $tmp['form']->getMessages();
                $pageValues = $tmp['form']->getData();
            }
        }
        
        $tmp = $this->pagesModel->getPageData();
        $template = $tmp['template'];
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/page_form_view.phtml',
            'data' => array(
                'task' => 'edit',
                'pageId' => $pageId,
                'pageFormConfig' => $pageFormConfig,
                'pageValues' => $pageValues,
                'templateId' => $template,
                'pageFormMsg' => $pageFormMsg,                
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