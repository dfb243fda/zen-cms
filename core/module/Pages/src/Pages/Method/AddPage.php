<?php

namespace Pages\Method;

use App\Method\AbstractMethod;
use Zend\Validator\AbstractValidator;

class AddPage extends AbstractMethod
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
        
        AbstractValidator::setDefaultTranslator($this->rootServiceLocator->get('translator'));
    }


    public function main()
    {
        $result = array();   
        
        if ($this->params()->fromRoute('domainId')) {
            $domainId = (int)$this->params()->fromRoute('domainId');
            $this->pagesModel->setDomainId($domainId);
        } elseif ($this->params()->fromRoute('pageId')) {
            $parentId = (int)$this->params()->fromRoute('pageId');
            $this->pagesModel->setParentPageId($parentId);
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
        
        
        $form = $this->pagesModel->getPageForm();
        
        $pageFormConfig = $form['formConfig'];
        $pageValues = $form['formValues'];
        
        $pageFormMsg = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->pagesModel->addPage($this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Страница успешно добавлена');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Pages',
                        'method' => 'EditPage',
                        'id' => $tmp['pageId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Страница успешно добавлена',
                );         
            } else {
                $result['success'] = false;
                $pageFormMsg = $tmp['form']->getMessages();
                $pageValues = $tmp['form']->getData();
            }
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/page_form_view.phtml',
            'data' => array(
                'task' => 'add',
                'pageFormConfig' => $pageFormConfig,
                'pageValues' => $pageValues,
                'pageFormMsg' => $pageFormMsg,
            ),
        );
        
        if ($this->params()->fromRoute('domainId')) {
            $result['contentTemplate']['data']['domainId'] = (int)$this->params()->fromRoute('domainId');
        } elseif ($this->params()->fromRoute('pageId')) {
            $result['contentTemplate']['data']['pageId'] = (int)$this->params()->fromRoute('pageId');
        }
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
        return $result;
    }
}