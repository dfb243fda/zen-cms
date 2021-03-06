<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class EditPage extends AbstractMethod
{    
    public function main()
    {        
        $pageEntity = $this->serviceLocator->get('Pages\Entity\Page');
        
        $request = $this->serviceLocator->get('request');
        $translator = $this->serviceLocator->get('translator');
        
        $result = array();   
        
        if ($this->params()->fromRoute('id')) {
            $pageId = (int)$this->params()->fromRoute('id');
            $pageEntity->setPageId($pageId);
        } else {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $pageEntity->setObjectTypeId($objectTypeId);
        }
        if (null !== $this->params()->fromRoute('pageTypeId')) {  
            $pageTypeId = (int)$this->params()->fromRoute('pageTypeId');
            $pageEntity->setPageTypeId($pageTypeId);
        }        
                        
        if ($request->isPost()) {
            $form = $pageEntity->getForm(false);
            
            $data = $request->getPost()->toArray();
            if (empty($data['common']['name'])) {
                $data['common']['name'] = $translator->translate('Pages:(Page without name)');
            }
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($pageEntity->editPage($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
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
                    $result['errMsg'] = 'При обновлении страницы произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        } else {
            $form = $pageEntity->getForm(true);
        }
        
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/page_form_view.phtml',
            'data' => array(
                'task' => 'edit',
                'pageId' => $pageId,
                'templateId' => $form->get('additional_params')->get('template')->getValue(),
                'form' => $form,           
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