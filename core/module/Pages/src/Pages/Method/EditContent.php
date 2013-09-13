<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class EditContent extends AbstractMethod
{
    public function main()
    {
        $contentEntity = $this->serviceLocator->get('Pages\Entity\Content');
        
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();   
        
        if ($this->params()->fromRoute('id')) {
            $contentId = (int)$this->params()->fromRoute('id');
            $contentEntity->setContentId($contentId);
        } else {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $contentEntity->setObjectTypeId($objectTypeId);
        }
        if (null !== $this->params()->fromRoute('pageContentTypeId')) {  
            $pageContentTypeId = (int)$this->params()->fromRoute('pageContentTypeId');
            $contentEntity->setContentTypeid($pageContentTypeId);
        }        
        
        
        
        if ($request->isPost()) {
            $form = $contentEntity->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('Pages:(Page content without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($contentEntity->editContent($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
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
                    $result['errMsg'] = 'При обновлении содержимого произошли ошибки';
                }
            } else {                
                $result['success'] = false;
            }            
        } else {
            $form = $contentEntity->getForm(true);
        }        
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/content_form_view.phtml',
            'data' => array(
                'task' => 'edit',
                'form' => $form,
                'contentId' => $contentId,
            ),
        );
        
        if ($this->flashMessenger()->hasSuccessMessages()) {
            $result['msg'] = $this->flashMessenger()->getSuccessMessages();
        } 
        if ($this->flashMessenger()->hasErrorMessages()) {
            $result['errMsg'] = $this->flashMessenger()->getErrorMessages();
        }
        
        $tmp = $contentEntity->getContentFormData();
        $pageId = $tmp['page_id'];
        
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