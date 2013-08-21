<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class AddContent extends AbstractMethod
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
        
        if (null === $this->params()->fromRoute('markerId')) {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        $markerId = (int)$this->params()->fromRoute('markerId');
        $this->contentModel->setMarkerId($markerId);
            
        if (null === $this->params()->fromRoute('beforeContentId')) {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        $beforeContentId = (int)$this->params()->fromRoute('beforeContentId');
        
        if (null === $this->params()->fromRoute('pageId')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $pageId = (int)$this->params()->fromRoute('pageId');
        $this->contentModel->setPageId($pageId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $this->contentModel->setObjectTypeId($objectTypeId);
        }
        if (null !== $this->params()->fromRoute('pageContentTypeId')) {  
            $pageContentTypeId = (int)$this->params()->fromRoute('pageContentTypeId');
            $this->contentModel->setPageContentTypeid($pageContentTypeId);
        }
        
        $form = $this->contentModel->getPageContentForm();
        
        $contentFormConfig = $form['formConfig'];
        $contentValues     = $form['formValues'];
        $contentFormMsg    = array();
        
        if ($this->request->isPost()) {
            $tmp = $this->contentModel->addContent($beforeContentId, $this->request->getPost());
            if ($tmp['success']) {
                if (!$this->request->isXmlHttpRequest()) {
                    $this->flashMessenger()->addSuccessMessage('Содержимое успешно добавлено');
                    $this->redirect()->toRoute('admin/method',array(
                        'module' => 'Pages',
                        'method' => 'EditContent',
                        'id'     => $tmp['contentId'],
                    ));
                }

                return array(
                    'success' => true,
                    'msg' => 'Содержимое успешно добавлено',
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
                'task'              => 'add',
                'contentFormConfig' => $contentFormConfig,
                'contentValues'     => $contentValues,
                'markerId'          => $markerId,
                'beforeContentId'   => $beforeContentId,
                'pageId'            => $pageId,
                'contentFormMsg'    => $contentFormMsg,
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
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