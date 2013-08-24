<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class AddPage extends AbstractMethod
{
    public function main()
    {
        $pagesCollection = $this->serviceLocator->get('Pages\Collection\Pages');
        
        $request = $this->serviceLocator->get('request');
        
        $result = array();   
        
        if ($this->params()->fromRoute('domainId')) {
            $domainId = (int)$this->params()->fromRoute('domainId');
            $pagesCollection->setDomainId($domainId);
        } elseif ($this->params()->fromRoute('pageId')) {
            $parentId = (int)$this->params()->fromRoute('pageId');
            $pagesCollection->setParentPageId($parentId);
        } else {
            $result['errMsg'] = array('Не переданы все необходимые параметры');
            return $result;
        }
        
        if (null !== $this->params()->fromRoute('objectTypeId')) {
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $pagesCollection->setObjectTypeId($objectTypeId);
        }
        if (null !== $this->params()->fromRoute('pageTypeId')) {  
            $pageTypeId = (int)$this->params()->fromRoute('pageTypeId');
            $pagesCollection->setPageTypeId($pageTypeId);
        }
        
        $form = $pagesCollection->getForm();
                
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                if ($pageId = $pagesCollection->addPage($form->getData())) {
                    if (!$request->isXmlHttpRequest()) {
                        $this->flashMessenger()->addSuccessMessage('Страница успешно создана');
                        $this->redirect()->toRoute('admin/method',array(
                            'module' => 'Pages',
                            'method' => 'EditPage',
                            'id' => $pageId,
                        ));
                    }
                    
                    return array(
                        'success' => true,
                        'msg' => 'Страница успешно создана',
                    );    
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = 'При создании страницы произошли ошибки';
                }
            } else {
                $result['success'] = false;
            }
        }
                
        $result['contentTemplate'] = array(
            'name' => 'content_template/Pages/page_form_view.phtml',
            'data' => array(
                'task' => 'add',
                'form' => $form,           
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