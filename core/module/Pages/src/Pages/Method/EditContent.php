<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class EditContent extends AbstractMethod
{
    protected $rootServiceLocator;
    
    protected $translator;
    
    protected $contentModel;
    
    protected $request;
    
    public function init()
    {
        $this->rootServiceLocator = $this->serviceLocator->getServiceLocator();
        $this->translator = $this->rootServiceLocator->get('translator');
        $this->contentModel = new \Pages\Model\PagesContent($this->rootServiceLocator);
        $this->request = $this->rootServiceLocator->get('request');
    }


    public function main()
    {
        $result = array();   
        
        if ($this->params()->fromRoute('id')) {
            $contentId = (int)$this->params()->fromRoute('id');
        } else {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $this->contentModel->setObjectTypeId($objectTypeId);
        }
        if (null !== $this->params()->fromRoute('pageContentTypeId')) {  
            $pageContentTypeId = (int)$this->params()->fromRoute('pageContentTypeId');
            $this->contentModel->setPageContentTypeid($pageContentTypeId);
        }
        
        
        $form = $this->contentModel->getPageContentForm($contentId);
        
        $contentFormConfig = $form['formConfig'];
        $contentValues = $form['formValues'];
        
        $contentFormMsg = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->contentModel->editContent($contentId, $this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Содержимое успешно обновлено');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Pages',
                        'method' => 'EditContent',
                        'id'     => $contentId,
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Содержимое успешно обновлено',
                );         
            } else {
                $result['success'] = false;
                $contentFormMsg = $tmp['form']->getMessages();
                $contentValues = $tmp['form']->getData();
            }
        }
        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/content_form_view.phtml',
            'data' => array(
                'task' => 'edit',
                'contentFormConfig' => $contentFormConfig,
                'contentValues'     => $contentValues,
                'contentId'         => $contentId,
                'contentFormMsg'    => $contentFormMsg,
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
        $tmp = $this->contentModel->getPageContentData();
        $pageId = $tmp['page_id'];
        
        $result['breadcrumbPrevLink'] = array(
            'title' => $this->translator->translate('Edit page method'),
            'link' => $this->url()->fromRoute('admin/method', array(
                'module' => 'Pages',
                'method' => 'EditPage',
                'id'     => $pageId,
            )),
        );
        
        return $result;
    }
}