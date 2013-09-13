<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class AddContent extends AbstractMethod
{
    public function main()
    {
        $contentCollection = $this->serviceLocator->get('Pages\Collection\Content');
        
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();  
        
        if (null === $this->params()->fromRoute('markerId')) {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        $markerId = (int)$this->params()->fromRoute('markerId');
        $contentCollection->setMarkerId($markerId);
        
        if (null === $this->params()->fromRoute('beforeContentId')) {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        $beforeContentId = (int)$this->params()->fromRoute('beforeContentId');
        $contentCollection->setBeforeContentId($beforeContentId);
        
        if (null === $this->params()->fromRoute('pageId')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            return $result;
        }
        $pageId = (int)$this->params()->fromRoute('pageId');
        $contentCollection->setPageId($pageId);
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $contentCollection->setObjectTypeId($objectTypeId);
        }
        if (null !== $this->params()->fromRoute('pageContentTypeId')) {  
            $contentTypeId = (int)$this->params()->fromRoute('pageContentTypeId');
            $contentCollection->setContentTypeId($contentTypeId);
        }
             
        if ($request->isPost()) {
            $form = $contentCollection->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('Pages:(Page content without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($contentId = $contentCollection->addContent($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Содержимое успешно добавлено');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Pages',
                            'method' => 'EditContent',
                            'id'     => $contentId,
                        ));
                    }

                    return array(
                        'success' => true,
                        'msg' => 'Содержимое успешно добавлено',
                    );         
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При обновлении содержимого произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }            
        } else {
            $form = $contentCollection->getForm(true);
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/content_form_view.phtml',
            'data' => array(
                'task'            => 'add',
                'form'            => $form,
                'markerId'        => $markerId,
                'beforeContentId' => $beforeContentId,
                'pageId'          => $pageId,
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
                
        $result['breadcrumbPrevLink'] = array(
            'title' => $translator->translate('Edit page method'),
            'link' => $this->url()->fromRoute('admin/method', array(
                'module' => 'Pages',
                'method' => 'EditPage',
                'id'     => $pageId,
            )),
        );
        
        return $result;
    }
}